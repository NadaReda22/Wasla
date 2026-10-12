<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Setting;
use Carbon\Carbon;

class BookingWhatsAppMessage
{
    /**
     * يرجع الإيموجي باستخدام Unicode escapes (\u{...}) لضمان ترميز UTF-8 سليم
     * بغض النظر عن ترميز ملف الـ PHP.
     */
    public static function icon(string $name): string
    {
        $icons = [
            'alert'  => "\u{1F6A8}",          // 🚨
            'out'    => "\u{1F6EB}",          // 🛫
            'in'     => "\u{1F6EC}",          // 🛬
            'date'   => "\u{1F4C5}",          // 📅
            'time'   => "\u{23F0}",           // ⏰
            'bus'    => "\u{1F690}",          // 🚐
            'driver' => "\u{1F464}",          // 👤
            'phone'  => "\u{1F4F1}",          // 📱
            'tour'   => "\u{1F6A9}",          // 🚩
            'ref'    => "\u{1F516}",          // 🔖
            'pax'    => "\u{1F4BA}",          // 💺
            'area'   => "\u{1F5FA}\u{FE0F}",  // 🗺️
            'address'=> "\u{1F4CD}",          // 📍
            'hotel'  => "\u{1F3E8}",          // 🏨
            'map'    => "\u{1F4CC}",          // 📌
            'meet'   => "\u{1F91D}",          // 🤝
            'room'   => "\u{1F6AA}",          // 🚪
            'cash'   => "\u{1F4B0}",          // 💰
            'wave'   => "\u{1F44B}",          // 👋
            'money'  => "\u{1F4B5}",          // 💵
            'pin'    => "\u{1F4CC}",          // 📌
            'pray'   => "\u{1F64F}",          // 🙏
            'star'   => "\u{1F31F}",          // 🌟
            'road'   => "\u{1F6E3}\u{FE0F}",  // 🛣️
        ];

        return $icons[$name] ?? '';
    }

    /**
     * Returns the value, or "N/A" when it is empty.
     */
    private static function na(mixed $value): string
    {
        return filled($value) ? (string) $value : 'N/A';
    }

    /**
     * Google Maps URL that opens the PLACE CARD (name, photos, rating) when the
     * hotel name matches a Google place, e.g. "Parrotel Beach Resort".
     * Falls back to the plain coordinates only when there is no hotel name.
     * Returns null when there is neither a name nor coordinates.
     */
    public static function mapUrl(Booking $record): ?string
    {
        $base = 'https://www.google.com/maps/search/?api=1&query=';
        $name = trim((string) $record->pickup_name);

        if ($name !== '') {
            $area  = trim((string) $record->area?->name_en);
            $query = implode(', ', array_filter([$name, $area, 'Sharm El Sheikh']));

            return $base . rawurlencode($query);
        }

        if ($record->pickup_lat && $record->pickup_lng) {
            return $base . rawurlencode("{$record->pickup_lat},{$record->pickup_lng}");
        }

        return null;
    }

    /**
     * Same as mapUrl() but returns "N/A" when there are no coordinates.
     */
    private static function mapLink(Booking $record): string
    {
        return self::mapUrl($record) ?? 'N/A';
    }

    /**
     * Builds the WhatsApp link depending on the device:
     * - Mobile  -> api.whatsapp.com (opens the WhatsApp app)
     * - Desktop -> web.whatsapp.com (WhatsApp Web, keeps emojis intact)
     */
    public static function waLink(string $phone, string $text): string
    {
        $query = "?phone={$phone}&text=" . rawurlencode($text);

        $isMobile = (bool) preg_match(
            '/Android|iPhone|iPad|iPod|Mobile/i',
            request()->userAgent() ?? ''
        );

        return $isMobile
            ? "https://api.whatsapp.com/send{$query}"
            : "https://web.whatsapp.com/send{$query}";
    }

    // ==========================================================
    // DRIVER
    // ==========================================================

    public static function driverText(Booking $record, Vehicle $driverVehicle, string $leg = 'outbound'): string
    {
        $date = Carbon::parse($record->booking_date)->format('M d, Y');

        $outTime = $record->departure?->departure_time ? Carbon::parse($record->departure->departure_time)->format('g:i A') : '09:00 AM';
        $retTime = $record->departure?->return_time ? Carbon::parse($record->departure->return_time)->format('g:i A') : 'TBD';

        $time = $leg === 'outbound' ? $outTime : $retTime;

        $tourName = self::na($record->tour?->name_en);
        $tourFullTime = "{$outTime} - {$retTime}";

        $cash = ($leg === 'outbound' && $record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? (float) $record->cash_due_on_board : 0;

        $hotelName     = self::na($record->pickup_name);
        $areaName      = self::na($record->area?->name_en);
        $fullAddress   = self::na($record->pickup_address ?? null);
        $mapLocation   = self::mapLink($record);
        $receptionText = !empty($record->reception_number) ? $record->reception_number : 'Main Lobby / Reception';
        $roomNumber    = self::na($record->room_number);

        $text = self::icon('alert') . " *SINGLE TRANSFER ASSIGNMENT* " . self::icon('alert') . "\n\n";

        if ($leg === 'outbound') {
            $text .= self::icon('out') . " *DIRECTION: GOING TO TOUR (Outbound)*\n";
        } else {
            $text .= self::icon('in') . " *DIRECTION: RETURNING (Inbound)*\n";
        }

        $text .= self::icon('date') . " *Date:* {$date}\n";
        $text .= self::icon('time') . " *Time:* {$time}\n";

        if ($record->tour?->is_bus_staying) {
            $text .= self::icon('bus') . " *WAITING:* Vehicle stays on-site during the tour!\n";
        }

        $text .= "\n" . self::icon('driver') . " *Driver:* " . self::na($driverVehicle->driver_name) . " (" . self::icon('phone') . " " . self::na($driverVehicle->driver_phone) . ")\n";
        $text .= self::icon('tour') . " *Tour:* {$tourName} ({$tourFullTime})\n\n";

        $text .= self::icon('driver') . " *Client:* " . self::na($record->client?->full_name) . "\n";
        $text .= self::icon('ref') . " *Ref:* " . self::na($record->booking_reference) . "\n";
        $text .= self::icon('pax') . " *Pax:* " . self::na($record->seats_count) . "\n\n";

        $text .= self::icon('hotel') . " *Hotel:* {$hotelName}\n";
        $text .= self::icon('area') . " *Area:* {$areaName}\n";
        $text .= self::icon('address') . " *Address:* {$fullAddress}\n";
        $text .= self::icon('map') . " *Location:* {$mapLocation}\n\n";

        $text .= self::icon('meet') . " *Meet At:* {$receptionText}\n";
        $text .= self::icon('room') . " *Room:* {$roomNumber}\n";

        if ($cash > 0) {
            $text .= "\n" . self::icon('cash') . " *Collect Cash:* $" . number_format($cash, 2) . "\n";
        }

        return $text;
    }

    public static function forDriver(Booking $record, Vehicle $driverVehicle, string $leg = 'outbound'): string
    {
        $driverPhone = preg_replace('/[^0-9]/', '', $driverVehicle->driver_phone);

        return self::waLink($driverPhone, self::driverText($record, $driverVehicle, $leg));
    }

    // ==========================================================
    // CLIENT
    // ==========================================================

    public static function clientText(Booking $record, array $legs = ['outbound', 'return']): string
    {
        $withOutbound = in_array('outbound', $legs, true);
        $withReturn   = in_array('return', $legs, true) && $record->trip_type === 'round_trip';
        $returnOnly   = ! $withOutbound && $withReturn;

        $brandName = Setting::first()?->brand_name ?? 'Our Company';

        $outTime = $record->departure?->departure_time ? Carbon::parse($record->departure->departure_time)->format('g:i A') : '09:00 AM';
        $retTime = $record->departure?->return_time ? Carbon::parse($record->departure->return_time)->format('g:i A') : 'TBD';
        $cleanDate = Carbon::parse($record->booking_date)->format('M d, Y');

        $tourName = $record->tour?->name_ru ? "{$record->tour->name_en} / {$record->tour->name_ru}" : self::na($record->tour?->name_en);
        $tourFullTime = "{$outTime} - {$retTime}";

        $cash = ($withOutbound && $record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? (float) $record->cash_due_on_board : 0;

        $hotelName     = self::na($record->pickup_name);
        $areaName      = self::na($record->area?->name_en);
        $fullAddress   = self::na($record->pickup_address ?? null);
        $mapLocation   = self::mapLink($record);
        $receptionText = !empty($record->reception_number) ? $record->reception_number : 'Main Lobby / Reception';
        $roomNumber    = self::na($record->room_number);

        $vehicleSection = "";

        if ($withOutbound) {
            if ($record->outboundVehicle) {
                $vType = $record->outboundVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus';
                $driverName = self::na($record->outboundVehicle->driver_name);
                $driverPhone = " (" . self::icon('phone') . " " . self::na($record->outboundVehicle->driver_phone) . ")";

                $vehicleSection .= self::icon('out') . " *Outbound [{$outTime}]:* {$vType} (" . self::na($record->outboundVehicle->plate_number) . ")\n";
                $vehicleSection .= self::icon('driver') . " *Driver:* {$driverName}{$driverPhone}\n";
            } else {
                $vehicleSection .= self::icon('out') . " *Outbound [{$outTime}]:* (Vehicle will be assigned shortly)\n";
            }
        }

        if ($withReturn) {
            if ($record->returnVehicle) {
                $retVType = $record->returnVehicle->type === 'private_car' ? 'Private Car' : 'Shared Microbus';
                $retDriverName = self::na($record->returnVehicle->driver_name);
                $retDriverPhone = " (" . self::icon('phone') . " " . self::na($record->returnVehicle->driver_phone) . ")";

                $vehicleSection .= "\n" . self::icon('in') . " *Return [{$retTime}]:* {$retVType} (" . self::na($record->returnVehicle->plate_number) . ")\n";
                $vehicleSection .= self::icon('driver') . " *Driver:* {$retDriverName}{$retDriverPhone}\n";
            } else {
                $vehicleSection .= "\n" . self::icon('in') . " *Return [{$retTime}]:* (Vehicle will be assigned shortly)\n";
            }
        }

        $text = "Hello " . self::na($record->client->full_name) . "! " . self::icon('wave') . "\n\n";

        if ($returnOnly) {
            $text .= "Your return transfer with *{$brandName}* is confirmed! Here are the details:\n\n";
        } else {
            $text .= "Your upcoming adventure with *{$brandName}* is confirmed! Here are your complete details:\n\n";
        }

        $text .= self::icon('ref') . " *Booking Ref:* " . self::na($record->booking_reference) . "\n";
        $text .= self::icon('tour') . " *Tour:* {$tourName} ({$tourFullTime})\n";
        $text .= self::icon('date') . " *Date:* {$cleanDate}\n\n";

        $text .= self::icon('hotel') . " *Hotel:* {$hotelName}\n";
        $text .= self::icon('area') . " *Area:* {$areaName}\n";
        $text .= self::icon('address') . " *Address:* {$fullAddress}\n";
        $text .= self::icon('map') . " *Location:* {$mapLocation}\n\n";

        $text .= self::icon('room') . " *Room:* {$roomNumber}\n";
        $text .= self::icon('meet') . " *Meet At:* {$receptionText}\n";
        $text .= self::icon('pax') . " *Passengers:* " . self::na($record->seats_count) . " Pax\n";

        if (!empty($vehicleSection)) {
            $text .= "\n" . trim($vehicleSection) . "\n";
        }

        if ($cash > 0) {
            $text .= "\n" . self::icon('money') . " *Remaining Balance:* $" . number_format($cash, 2) . " (Please prepare this in cash for the driver)\n";
        }

        $text .= "\n" . self::icon('pin') . " *Important Instructions:*\n";

        if ($returnOnly) {
            $text .= "- Please be ready at the tour's meeting point for your return transfer at *{$retTime}*.\n";
            $text .= "- Kindly note that the vehicle might arrive slightly late due to traffic. Your patience is highly appreciated! " . self::icon('pray') . "\n\n";
        } else {
            $text .= "- Please be waiting in the reception or lobby area *10 to 15 minutes* before your scheduled pickup time.\n";
            $text .= "- Kindly note that the vehicle might arrive slightly late due to traffic or previous hotel pickups. Your patience is highly appreciated! " . self::icon('pray') . "\n";
            
            if ($record->trip_type === 'round_trip' && !$withReturn) {
                $text .= "- ⏳ *Note:* Your return transfer details (Driver & Vehicle) will be sent to you in a separate message later.\n";
            }
            $text .= "\n";
        }

        $text .= "Thank you for choosing {$brandName}! " . self::icon('star');

        return $text;
    }

    public static function forClient(Booking $record): string
    {
        $phone = preg_replace('/[^0-9]/', '', $record->client->whatsapp_number);

        return self::waLink($phone, self::clientText($record));
    }
}