<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Setting;
use Carbon\Carbon;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Database\Eloquent\Collection;

class WhatsAppFormatterService
{
    public function buildClientConfirmation(Booking $record): string
    {
        $brandName = Setting::first()?->brand_name ?? 'Our Company';
        
        $pickupTime = $record->departure?->formatted_departure_time ?? '09:00 AM';
        $returnTime = $record->departure?->return_time ? $record->departure->formatted_return_time : null;
        $cleanDate = Carbon::parse($record->booking_date)->format('M d, Y');
        
        $timingText = "🕒 *Pickup Time:* {$pickupTime}";
        if ($record->trip_type === 'round_trip' && $returnTime) {
            $timingText .= " | *Return Time:* {$returnTime}";
        }

        $receptionText = !empty($record->reception_number) ? $record->reception_number : 'Main Lobby / Reception';
        $tourName = $record->tour?->name_ru ? "{$record->tour->name_en} / {$record->tour->name_ru}" : ($record->tour?->name_en ?? 'Custom Tour');
        $cash = ($record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? $record->cash_due_on_board : 0;
        
        $vehicleSection = "";
        if ($record->outboundVehicle) {
            $vType = $record->outboundVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus';
            $driverName = $record->outboundVehicle->driver_name ?? 'Assigned Driver';
            $driverPhone = $record->outboundVehicle->driver_phone ? " (📱 {$record->outboundVehicle->driver_phone})" : '';
            
            $vehicleSection .= "🚐 *Outbound Vehicle:* {$vType} ({$record->outboundVehicle->plate_number})\n";
            $vehicleSection .= "👤 *Outbound Driver:* {$driverName}{$driverPhone}\n";
        } else {
            $vehicleSection .= "🚐 *Vehicle:* (Will be assigned shortly)\n";
        }

        if ($record->trip_type === 'round_trip') {
            if ($record->returnVehicle) {
                $retVType = $record->returnVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus';
                $retDriverName = $record->returnVehicle->driver_name ?? 'Assigned Driver';
                $retDriverPhone = $record->returnVehicle->driver_phone ? " (📱 {$record->returnVehicle->driver_phone})" : '';
                
                $vehicleSection .= "\n🛬 *Return Vehicle:* {$retVType} ({$record->returnVehicle->plate_number})\n";
                $vehicleSection .= "👤 *Return Driver:* {$retDriverName}{$retDriverPhone}\n";
            } elseif ($record->outboundVehicle && $record->tour?->is_bus_staying) {
                $vehicleSection .= "\n🛬 *Return Vehicle:* Same vehicle (stays on-site)\n";
            }
        }

        $message = "Hello {$record->client->full_name}! 👋\n\n";
        $message .= "Your upcoming adventure with *{$brandName}* is confirmed! Here are your complete details:\n\n";
        $message .= "🚩 *Tour:* {$tourName}\n📅 *Date:* {$cleanDate}\n{$timingText}\n📍 *Location:* {$record->pickup_name}\n";
        
        if (!empty($record->room_number)) $message .= "🚪 *Room:* {$record->room_number}\n";
        
        $message .= "🤝 *Meet At:* {$receptionText}\n💺 *Passengers:* {$record->seats_count} Pax\n";
        
        if (!empty($vehicleSection)) $message .= "\n" . trim($vehicleSection) . "\n";
        if ($cash > 0) $message .= "\n💵 *Remaining Balance:* $" . number_format($cash, 2) . " (Please prepare this in cash for the driver)\n";

        $message .= "\n📌 *Important Instructions:*\n";
        $message .= "• Please be waiting in the reception or lobby area *10 to 15 minutes* before your scheduled pickup time.\n";
        $message .= "• Kindly note that the vehicle might arrive slightly late due to traffic or previous hotel pickups. Your patience is highly appreciated! 🙏\n\n";
        $message .= "Thank you for choosing {$brandName}! 🌟";

        return $message;
    }


    public function buildDriverManifest(\Illuminate\Database\Eloquent\Collection $records, \App\Models\Vehicle $driverVehicle, string $targetDriverId, string $cleanDate): string
    {
        $driverName = $driverVehicle->driver_name ?? 'Unknown Driver';
        $driverPlate = $driverVehicle->plate_number ?? 'No Plate';
        
        $driverRecords = $records->filter(fn ($r) => $r->outbound_vehicle_id === $targetDriverId || $r->return_vehicle_id === $targetDriverId);
        $manifestVehicleType = !empty($driverVehicle->type) ? ($driverVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus') : ($driverRecords->isNotEmpty() ? \App\Filament\Resources\BookingResource::vehicleTypeLabel($driverRecords->first()) : null);
        
        $text = \App\Filament\Resources\BookingResource::dateBanner($cleanDate) . 
                \App\Filament\Resources\BookingResource::driverInfoBlock($driverName, $driverVehicle->driver_phone, $driverPlate, $manifestVehicleType) . 
                "*ROUTE MANIFEST*\n════════════════════\n\n";

        $dailyTotalCash = 0; $dailyOutboundPax = 0; $dailyReturnPax = 0;
        
        $groupedByTour = $driverRecords->groupBy('tour_id')->sortBy(function ($group) { 
            return $group->first()->departure?->departure_time ?? '99:99'; 
        });

        foreach ($groupedByTour as $tourId => $tourGroup) {
            $sampleRecord = $tourGroup->first(); 
            $tour = $sampleRecord->tour;
            
            $outboundRecords = $tourGroup->filter(fn ($r) => $r->outbound_vehicle_id === $targetDriverId); 
            $returnRecords = $tourGroup->filter(fn ($r) => $r->return_vehicle_id === $targetDriverId);
            
            $goTime = $sampleRecord->departure?->departure_time ? \Carbon\Carbon::parse($sampleRecord->departure->departure_time)->format('g:i A') : '09:00 AM'; 
            $retTime = $sampleRecord->departure?->return_time ? \Carbon\Carbon::parse($sampleRecord->departure->return_time)->format('g:i A') : 'TBD';
            
            $staysLabel = \App\Filament\Resources\BookingResource::staysLabel($tour, $retTime); 
            $tourName = $tour?->name_ru ? "{$tour->name_en} / {$tour->name_ru}" : ($tour?->name_en ?? 'Unknown');
            
            $text .= "┌────────────────────┐\n│ 🚩 *{$tourName}*\n│ 🕐 _Out: {$goTime} | Ret: {$retTime}_\n│ 🅿️ *Stays:* {$staysLabel}\n└────────────────────┘\n";
            
            $tourStops = 0; $tourPax = 0; $tourCash = 0;
            
            if ($outboundRecords->isNotEmpty()) {
                $text .= "🛫 *[ GOING TO TOUR ]* — " . $outboundRecords->count() . " stop(s)\n⏰ *Pickup Time:* {$goTime}\n\n"; 
                $stopNum = 1; $groupPax = 0; $groupCash = 0;
                
                foreach ($outboundRecords as $record) {
                    $cash = ($record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? $record->cash_due_on_board : 0;
                    $groupPax += $record->seats_count; $groupCash += $cash; $dailyOutboundPax += $record->seats_count; $dailyTotalCash += $cash; $tourStops++; $tourPax += $record->seats_count; $tourCash += $cash;
                    
                    $text .= \App\Filament\Resources\BookingResource::clientStopLine($record, $stopNum); 
                    $text .= "_Outbound subtotal: {$groupPax} pax | $" . number_format($groupCash, 2) . "_\n\n";
                    $stopNum++;
                }
            }
            
            if ($returnRecords->isNotEmpty()) {
                $text .= "🛬 *[ RETURNING TO HOTEL ]* — " . $returnRecords->count() . " stop(s)\n⏰ *Return Time:* {$retTime}\n\n"; 
                $stopNum = 1; $groupPax = 0;
                
                foreach ($returnRecords as $record) {
                    $collectHere = $record->outbound_vehicle_id !== $targetDriverId; 
                    $cash = ($collectHere && $record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? $record->cash_due_on_board : 0;
                    
                    $groupPax += $record->seats_count; $dailyReturnPax += $record->seats_count; $dailyTotalCash += $cash; $tourStops++; $tourPax += $record->seats_count; $tourCash += $cash;
                    
                    $text .= \App\Filament\Resources\BookingResource::clientStopLine($record, $stopNum, $collectHere); 
                    $text .= "_Return subtotal: {$groupPax} pax_\n\n";
                    $stopNum++;
                }
            }
            
            $text .= "*📌 TOUR TOTAL:* {$tourStops} booking(s) | {$tourPax} pax | $" . number_format($tourCash, 2) . "\n════════════════════\n\n";
        }
        
        $text .= "*📊 DAILY TOTALS*\n▸ *Pax (Going):* {$dailyOutboundPax}\n▸ *Pax (Return):* {$dailyReturnPax}\n▸ *TOTAL CASH TO COLLECT: $" . number_format($dailyTotalCash, 2) . "*\n\nDrive safely! 🛣";
        
        return $text;
    }

    public function queueBulkConfirmations(Collection $bookings): int
    {
        $queuedCount = 0;

        foreach ($bookings as $record) {
            // Lock the booking
            $record->update(['is_distribution_ensured' => true]);

            if ($record->client && $record->client->whatsapp_number) {
                // The @c.us is required by the Green API format
                $phone = preg_replace('/[^0-9]/', '', $record->client->whatsapp_number) . '@c.us';
                
                // Get the message text using your existing method
                $message = $this->buildClientConfirmation($record);

                // Dispatch to the Laravel Queue
                \App\Jobs\SendWhatsAppMessageJob::dispatch($record->id, $phone, $message);
                $queuedCount++;
            }
        }

        return $queuedCount;
    }
}