<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\Page;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\Tour;
use App\Models\Area;
use App\Models\Setting;
use App\Jobs\SendWhatsAppMessageJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Filament\Actions\Action;                             
use Filament\Actions\Concerns\InteractsWithActions;       
use Filament\Actions\Contracts\HasActions;   
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Collection;
use App\Services\GreenApiService;

class BookingDistribution extends Page implements HasActions
{
    use InteractsWithActions;

    protected static string $resource = BookingResource::class;
    protected static string $view = 'filament.resources.booking-resource.pages.booking-distribution';
    
    public function getTitle(): string
    {
        return __('admin.actions.fleet_distribution') ?? 'Fleet Distribution';
    }

    public $selectedDate;
    public $selectedTour = ''; 
    public $selectedDepartureTime = ''; 
    public $selectedReturnTime = '';    
    public $selectedAreaId = '';
    public $direction = 'outbound'; 
    public $selectedVehicleType = ''; 
    public $selectedBookingVehicleType = ''; 
    public $selectedBookings = [];

    public function mount()
    {
        $this->selectedDate = Carbon::today()->toDateString();
    }

    public function updatedSelectedTour()
    {
        $this->selectedDepartureTime = '';
        $this->selectedReturnTime = '';
        $this->selectedAreaId = '';
        $this->selectedBookingVehicleType = '';
    }

    public function updatedSelectedDate()
    {
        $this->selectedDepartureTime = '';
        $this->selectedReturnTime = '';
        $this->selectedAreaId = '';
        $this->selectedBookingVehicleType = '';
    }

    public function updated($propertyName)
    {
        $filters = ['selectedDate', 'selectedTour', 'selectedDepartureTime', 'selectedReturnTime', 'selectedAreaId', 'direction', 'selectedBookingVehicleType'];
        if (in_array($propertyName, $filters)) {
            $this->selectedBookings = [];
        }

        $this->dispatch('refresh-map');
    }

    public function getAvailableDatesProperty()
    {
        $dates = [];
        for ($i = 0; $i < 5; $i++) {
            $date = Carbon::today()->addDays($i)->toDateString();
            $dates[$date] = Carbon::parse($date)->format('D, M d');
        }
        return $dates;
    }

    public function getAvailableToursProperty()
    {
        return Tour::withTrashed()->get()
            ->mapWithKeys(fn($t) => [$t->id => $t->name_ru ? "{$t->name_en} / {$t->name_ru}" : $t->name_en])
            ->toArray();
    }

    public function getAvailableDepartureTimesProperty()
    {
        if (!$this->selectedDate) return [];
        
        $query = Booking::where('booking_date', $this->selectedDate)
            ->with(['departure' => fn($q) => $q->withTrashed()]);

        if ($this->selectedTour) {
            $query->where('tour_id', $this->selectedTour);
        }

        $bookingTimes = $query->get()
            ->map(function($b) {
                if ($b->departure) return Carbon::parse($b->departure->departure_time)->format('H:i');
                return null;
            })
            ->filter()
            ->unique()
            ->toArray();

        $hasNullDep = (clone $query)->whereNull('tour_departure_id')->exists();

        if ($hasNullDep) {
            $bookingTimes[] = '09:00';
        }

        sort($bookingTimes);

        $formatted = [];
        foreach ($bookingTimes as $time) {
            $label = Carbon::createFromFormat('H:i', $time)->format('g:i A');
            $formatted[$label] = $label;
        }
        return $formatted;
    }

    public function getAvailableReturnTimesProperty()
    {
        if (!$this->selectedDate) return [];

        $bookingsQuery = Booking::where('booking_date', $this->selectedDate)
            ->where('trip_type', 'round_trip')
            ->with(['departure' => fn($q) => $q->withTrashed()]);

        if ($this->selectedTour) {
            $bookingsQuery->where('tour_id', $this->selectedTour);
        }

        if ($this->selectedDepartureTime) {
            $dbTime = Carbon::parse($this->selectedDepartureTime)->format('H:i');
            $bookingsQuery->whereHas('departure', fn($q) => $q->withTrashed()->where('departure_time', 'LIKE', $dbTime . '%'));
        }

        $bookingTimes = $bookingsQuery->get()
            ->map(fn($b) => $b->departure?->return_time ? Carbon::parse($b->departure->return_time)->format('H:i') : null)
            ->filter()
            ->unique()
            ->toArray();

        sort($bookingTimes);

        $formatted = [];
        foreach ($bookingTimes as $time) {
            $label = Carbon::createFromFormat('H:i', $time)->format('g:i A');
            $formatted[$label] = $label;
        }
        return $formatted;
    }

    public function getAvailableAreasProperty()
    {
        if (!$this->selectedDate) return [];

        $query = Booking::where('booking_date', $this->selectedDate)
            ->whereNotNull('area_id');

        if ($this->selectedTour) {
            $query->where('tour_id', $this->selectedTour);
        }

        if ($this->direction === 'outbound') {
            $query->whereNull('outbound_vehicle_id');
        } else {
            $query->where('trip_type', 'round_trip')->whereNull('return_vehicle_id');
        }

        if ($this->selectedDepartureTime) {
            $dbDepTime = Carbon::parse($this->selectedDepartureTime)->format('H:i');
            $query->where(function($q) use ($dbDepTime) {
                $q->whereHas('departure', fn($sub) => $sub->withTrashed()->where('departure_time', 'LIKE', $dbDepTime . '%'));
                if ($dbDepTime === '09:00') $q->orWhereNull('tour_departure_id');
            });
        }

        if ($this->selectedReturnTime) {
            $dbRetTime = Carbon::parse($this->selectedReturnTime)->format('H:i');
            $query->whereHas('departure', fn($sub) => $sub->withTrashed()->where('return_time', 'LIKE', $dbRetTime . '%'));
        }

        $areaIds = $query->pluck('area_id')->unique();
        return Area::whereIn('id', $areaIds)->pluck('name_en', 'id')->toArray();
    }

    public function getUnassignedBookingsProperty()
    {
        if (!$this->selectedDate) return collect();

        $query = Booking::with([
                'client', 'area', 
                'tour' => fn($q) => $q->withTrashed(), 
                'departure' => fn($q) => $q->withTrashed(),
                'pricingTier' => fn($q) => $q->withTrashed()
            ])
            ->where('booking_date', $this->selectedDate);

        if ($this->selectedTour) {
            $query->where('tour_id', $this->selectedTour);
        }

        if ($this->selectedAreaId) {
            $query->where('area_id', $this->selectedAreaId);
        }

        if ($this->selectedBookingVehicleType) {
            $query->whereHas('pricingTier', function($q) {
                $q->withTrashed()->where('vehicle_type', $this->selectedBookingVehicleType);
            });
        }

        if ($this->direction === 'outbound') {
            $query->whereNull('outbound_vehicle_id');
        } else {
            $query->where('trip_type', 'round_trip')->whereNull('return_vehicle_id');
        }

        if ($this->selectedDepartureTime) {
            $dbDepTime = Carbon::parse($this->selectedDepartureTime)->format('H:i');
            $query->where(function($q) use ($dbDepTime) {
                $q->whereHas('departure', fn($sub) => $sub->withTrashed()->where('departure_time', 'LIKE', $dbDepTime . '%'));
                if ($dbDepTime === '09:00') $q->orWhereNull('tour_departure_id');
            });
        }

        if ($this->selectedReturnTime) {
            $dbRetTime = Carbon::parse($this->selectedReturnTime)->format('H:i');
            $query->whereHas('departure', fn($sub) => $sub->withTrashed()->where('return_time', 'LIKE', $dbRetTime . '%'));
        }

        return $query->get();
    }

    public function getActiveVehiclesProperty()
    {
        $query = Vehicle::where('is_active', true);
        
        if ($this->selectedVehicleType) {
            $query->where('type', $this->selectedVehicleType);
        }

        $vehicles = $query->get();

        $selectedDate = $this->selectedDate;
        $selectedTour = $this->selectedTour;
        $direction = $this->direction;
        $targetVehicleCol = $direction === 'outbound' ? 'outbound_vehicle_id' : 'return_vehicle_id';
        
        $filterDepTime = $this->selectedDepartureTime ? Carbon::parse($this->selectedDepartureTime)->format('H:i') : null;
        $filterRetTime = $this->selectedReturnTime ? Carbon::parse($this->selectedReturnTime)->format('H:i') : null;

        return $vehicles->map(function ($vehicle) use ($selectedDate, $selectedTour, $direction, $targetVehicleCol, $filterDepTime, $filterRetTime) {
            
            $allTodayBookings = Booking::with([
                    'area', 
                    'tour' => fn($q) => $q->withTrashed(),
                    'departure' => fn($q) => $q->withTrashed()
                ])
                ->where('booking_date', $selectedDate)
                ->where(function($q) use ($vehicle) {
                    $q->where('outbound_vehicle_id', $vehicle->id)
                      ->orWhere('return_vehicle_id', $vehicle->id);
                })
                ->get();

            $vehicle->covered_zones = $allTodayBookings->pluck('area.name_en')->filter()->unique()->values()->toArray();

            $stayingBooking = $allTodayBookings->first(fn($b) => (bool) $b->tour?->is_bus_staying && $b->outbound_vehicle_id === $vehicle->id);
            if ($stayingBooking) {
                $vehicle->is_tour_staying = true;
                
                $depRaw = $stayingBooking->departure?->departure_time;
                $retRaw = $stayingBooking->departure?->return_time;
                
                $vehicle->staying_time_range = [
                    'out' => $depRaw ? Carbon::parse($depRaw)->format('g:i A') : '09:00 AM',
                    'ret' => $retRaw ? Carbon::parse($retRaw)->format('g:i A') : 'TBD'
                ];

                $vehicle->staying_tour_name = $stayingBooking->tour?->name_en ?? __('admin.options.custom_tour') ?? 'Custom Tour';
                $lockedZones = $allTodayBookings->where('tour_id', $stayingBooking->tour_id)->where('outbound_vehicle_id', $vehicle->id)->pluck('area.name_en')->filter()->unique()->implode(', ');
                $vehicle->staying_area_name = $lockedZones ?: __('admin.options.unknown_area') ?? 'Unknown Area';

            } else {
                $vehicle->is_tour_staying = false;
            }

            $assignments = [];

            $outbounds = $allTodayBookings->where('outbound_vehicle_id', $vehicle->id);
            $outboundGrouped = $outbounds->groupBy(fn($b) => $b->tour_id . '|' . ($b->tour_departure_id ?? 'default'));

            foreach ($outboundGrouped as $key => $tBookings) {
                $first = $tBookings->first();
                $rawTime = $first->departure?->departure_time;
                $depTime = $rawTime ? Carbon::parse($rawTime)->format('g:i A') : '09:00 AM';

                $tourNameDisplay = $first->tour ? ($first->tour->name_ru ? "{$first->tour->name_en} / {$first->tour->name_ru}" : $first->tour->name_en) : (__('admin.options.custom_tour') ?? 'Custom Tour');

                $assignments[] = [
                    'direction' => 'outbound',
                    'tour_id' => $first->tour_id,
                    'tour_name' => $tourNameDisplay,
                    'time' => $depTime,
                    'is_bus_staying' => (bool) $first->tour?->is_bus_staying,
                    'pax' => $tBookings->sum('seats_count'),
                    'booking_ids' => $tBookings->pluck('id')->toArray(),
                    'zones' => $tBookings->pluck('area.name_en')->filter()->unique()->values()->toArray(),
                ];
            }

            $returns = $allTodayBookings->where('return_vehicle_id', $vehicle->id);
            $returnGrouped = $returns->groupBy(fn($b) => $b->tour_id . '|' . ($b->tour_departure_id ?? 'default'));

            foreach ($returnGrouped as $key => $tBookings) {
                $first = $tBookings->first();
                $rawTime = $first->departure?->return_time;
                $retTime = $rawTime ? Carbon::parse($rawTime)->format('g:i A') : 'TBD';

                $tourNameDisplay = $first->tour ? ($first->tour->name_ru ? "{$first->tour->name_en} / {$first->tour->name_ru}" : $first->tour->name_en) : (__('admin.options.custom_tour') ?? 'Custom Tour');

                $assignments[] = [
                    'direction' => 'return',
                    'tour_id' => $first->tour_id,
                    'tour_name' => $tourNameDisplay,
                    'time' => $retTime,
                    'is_bus_staying' => (bool) $first->tour?->is_bus_staying,
                    'pax' => $tBookings->sum('seats_count'),
                    'booking_ids' => $tBookings->pluck('id')->toArray(),
                    'zones' => $tBookings->pluck('area.name_en')->filter()->unique()->values()->toArray(),
                ];
            }

            $vehicle->assignments_list = $assignments;

            $vehicle->pending_ensure_count = Booking::where('booking_date', $selectedDate)
                ->where('is_distribution_ensured', false)
                ->whereNotNull('outbound_vehicle_id')
                ->where(function ($q) {
                    $q->where('trip_type', 'one_way')
                      ->orWhereNotNull('return_vehicle_id');
                })
                ->where(function ($q) use ($vehicle) {
                    $q->where('outbound_vehicle_id', $vehicle->id)
                      ->orWhere('return_vehicle_id', $vehicle->id);
                })
                ->count();

            $currentDirectionBookings = $allTodayBookings->where($targetVehicleCol, $vehicle->id);
            if ($selectedTour) {
                $currentDirectionBookings = $currentDirectionBookings->where('tour_id', $selectedTour);
            }
            
            if ($filterDepTime) {
                $currentDirectionBookings = $currentDirectionBookings->filter(function($b) use ($filterDepTime) {
                    $val = $b->departure?->departure_time;
                    if (!$val && $filterDepTime === '09:00') return true;
                    return $val && str_contains($val, $filterDepTime);
                });
            }

            if ($filterRetTime) {
                $currentDirectionBookings = $currentDirectionBookings->filter(function($b) use ($filterRetTime) {
                    $val = $b->departure?->return_time;
                    return $val && str_contains($val, $filterRetTime);
                });
            }
            
            $vehicle->seats_taken = (int) $currentDirectionBookings->sum('seats_count');

            return $vehicle;
        })->values();
    }

    public function assignToVehicle($vehicleId)
    {
        if (empty($this->selectedBookings)) {
            Notification::make()->warning()->title(__('admin.notifications.select_bookings_first'))->send();
            return;
        }

        $ensuredBookings = Booking::whereIn('id', $this->selectedBookings)
            ->where('is_distribution_ensured', true)
            ->count();

        if ($ensuredBookings > 0) {
            Notification::make()->danger()
                ->title('Action Forbidden')
                ->body('You cannot reassign bookings that have already been ensured and locked.')
                ->send();
            return;
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle || !$vehicle->is_active) {
            Notification::make()->danger()->title(__('admin.notifications.invalid_vehicle'))->send();
            return;
        }

        $seatsNeeded = Booking::whereIn('id', $this->selectedBookings)->sum('seats_count');
        $activeVehicles = $this->getActiveVehiclesProperty();
        $targetVehicle = $activeVehicles->firstWhere('id', $vehicleId);
        
        $currentSeatsTaken = $targetVehicle ? $targetVehicle->seats_taken : 0;
        $remainingSeats = max(0, $vehicle->capacity - $currentSeatsTaken);

        if ($seatsNeeded > $remainingSeats) {
            Notification::make()->danger()
                ->title(__('admin.notifications.not_enough_capacity'))
                ->body(__('admin.notifications.not_enough_capacity_body', [
                    'needed' => $seatsNeeded,
                    'plate' => $vehicle->plate_number,
                    'left' => $remainingSeats
                ]))
                ->send();
            return;
        }

        $columnToUpdate = $this->direction === 'outbound' ? 'outbound_vehicle_id' : 'return_vehicle_id';

        DB::transaction(function () use ($columnToUpdate, $vehicleId) {
            Booking::whereIn('id', $this->selectedBookings)->update([
                $columnToUpdate => $vehicleId,
            ]);
        });

        $this->selectedBookings = [];

        Notification::make()->success()
            ->title(__('admin.notifications.bookings_assigned'))
            ->body(__('admin.notifications.bookings_assigned_body', [
                'direction' => $this->direction,
                'plate' => $vehicle->plate_number
            ]))
            ->send();
            
        $this->dispatch('refresh-map');
    }

    public function unassignBooking(array $bookingIds, string $direction)
    {
        $ensuredBookings = Booking::whereIn('id', $bookingIds)
            ->where('is_distribution_ensured', true)
            ->count();

        if ($ensuredBookings > 0) {
            Notification::make()->danger()
                ->title('Action Forbidden')
                ->body('You cannot unassign bookings that have already been ensured and locked.')
                ->send();
            return;
        }

        $col = $direction === 'outbound' ? 'outbound_vehicle_id' : 'return_vehicle_id';

        DB::transaction(function () use ($bookingIds, $col) {
            Booking::whereIn('id', $bookingIds)->update([
                $col => null,
            ]);
        });

        Notification::make()->success()
            ->title(__('admin.notifications.bookings_unassigned'))
            ->body(__('admin.notifications.bookings_unassigned_body', [
                'count' => count($bookingIds),
                'direction' => $direction
            ]))
            ->send();

        $this->dispatch('refresh-map');
    }

    // ===============================================
    // ASYNCHRONOUS WHATSAPP NOTIFICATION QUEUEING
    // ===============================================
    private function queueNotificationsForBookings(Collection $bookings): int
    {
        $queuedCount = 0;
        $brandName = Setting::first()?->brand_name ?? 'Our Company';

        foreach ($bookings as $record) {
            $record->update(['is_distribution_ensured' => true]);

            if ($record->client && $record->client->whatsapp_number) {
                $phone = preg_replace('/[^0-9]/', '', $record->client->whatsapp_number) . '@c.us';

                $pickupTime = $record->departure?->departure_time ? Carbon::parse($record->departure->departure_time)->format('g:i A') : '09:00 AM';
                $returnTime = $record->departure?->return_time ? Carbon::parse($record->departure->return_time)->format('g:i A') : null;
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
                $message .= "🚩 *Tour:* {$tourName}\n";
                $message .= "📅 *Date:* {$cleanDate}\n";
                $message .= "{$timingText}\n";
                $message .= "📍 *Location:* {$record->pickup_name}\n";
                
                // Room isolated from meeting point
                if (!empty($record->room_number)) {
                    $message .= "🚪 *Room:* {$record->room_number}\n";
                }
                
                $message .= "🤝 *Meet At:* {$receptionText}\n";
                $message .= "💺 *Passengers:* {$record->seats_count} Pax\n";
                
                if (!empty($vehicleSection)) {
                    $message .= "\n" . trim($vehicleSection) . "\n";
                }

                if ($cash > 0) {
                    $message .= "\n💵 *Remaining Balance:* $" . number_format($cash, 2) . " (Please prepare this in cash for the driver)\n";
                }

                $message .= "\n📌 *Important Instructions:*\n";
                $message .= "• Please be waiting in the reception or lobby area *10 to 15 minutes* before your scheduled pickup time.\n";
                $message .= "• Kindly note that the vehicle might arrive slightly late due to traffic or previous hotel pickups. Your patience is highly appreciated! 🙏\n\n";
                $message .= "Thank you for choosing {$brandName}! 🌟";

                // Dispatch to the Laravel Queue
                SendWhatsAppMessageJob::dispatch($phone, $message);
                $queuedCount++;
            }
        }

        return $queuedCount;
    }

    public function ensureAndNotifyDailyAction(): Action
    {
        return Action::make('ensureAndNotifyDaily')
            ->label(__('admin.actions.ensure_notify_all') ?? 'Ensure & Notify All')
            ->icon('heroicon-s-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('admin.distribution.confirm_heading') ?? 'Lock & Notify All Assigned Bookings')
            ->modalDescription(__('admin.distribution.confirm_body') ?? 'This will lock every fully assigned booking for the selected date and queue a WhatsApp confirmation for each client. Locked bookings can no longer be edited or reassigned.')
            ->modalSubmitActionLabel(__('admin.actions.confirm_lock_notify') ?? 'Yes, lock & notify')
            ->action(fn () => $this->ensureAndNotifyDaily());
    }  
    
    public function checkGreenApiStatusAction(): Action
    {
        return Action::make('checkGreenApiStatus')
            ->label('Check WhatsApp Status')
            ->icon('heroicon-o-signal')
            ->color('gray')
            ->action(function () {
                $service = app(GreenApiService::class);

                if (!$service->isConfigured()) {
                    Notification::make()
                        ->danger()
                        ->title('Green API not configured')
                        ->body('GREEN_API_INSTANCE_ID or GREEN_API_TOKEN is missing from .env.')
                        ->send();
                    return;
                }

                $state = $service->getInstanceState();

                match ($state) {
                    'authorized' => Notification::make()->success()->title('WhatsApp connected')->body('The instance is authorized and ready to send.')->send(),
                    'notAuthorized' => Notification::make()->danger()->title('WhatsApp not linked')->body('Scan the QR code in the Green API console to reconnect.')->send(),
                    'blocked' => Notification::make()->danger()->title('Instance blocked')->body('This Green API instance has been blocked. Check your console.')->send(),
                    default => Notification::make()->warning()->title('Could not verify status')->body('State: ' . ($state ?? 'unreachable') . '. Check the Laravel log for details.')->send(),
                };
            });
    }

    public function ensureAndNotifyDaily()
    {
        if (!$this->selectedDate) {
            Notification::make()->warning()->title('Please select a date first.')->send();
            return;
        }

        // STILL IMPORTANT: We verify the API is actually connected before pushing 50 messages to a queue that will fail.
        $greenApi = app(GreenApiService::class);
        if (!$greenApi->isAuthorized()) {
            Notification::make()
                ->danger()
                ->title('WhatsApp not connected')
                ->body('The Green API instance is not authorized. Bookings will NOT be locked until this is fixed — use "Check WhatsApp Status" above for details.')
                ->send();
            return;
        }

        $bookings = Booking::with(['client', 'tour', 'departure', 'outboundVehicle', 'returnVehicle'])
            ->where('booking_date', $this->selectedDate)
            ->where('is_distribution_ensured', false)
            ->whereNotNull('outbound_vehicle_id')
            ->where(function ($q) {
                $q->where('trip_type', 'one_way')
                  ->orWhereNotNull('return_vehicle_id');
            })
            ->when($this->selectedVehicleType, function ($q) {
                $q->where(function ($sub) {
                    $sub->whereHas('outboundVehicle', fn ($vq) => $vq->where('type', $this->selectedVehicleType))
                        ->orWhereHas('returnVehicle', fn ($vq) => $vq->where('type', $this->selectedVehicleType));
                });
            })
            ->get();

        if ($bookings->isEmpty()) {
            Notification::make()->warning()
                ->title('No pending bookings')
                ->body('No complete, unlocked assignments match the current filters for this date.')
                ->send();
            return;
        }

        $queuedCount = $this->queueNotificationsForBookings($bookings);

        $filterNote = $this->selectedVehicleType
            ? ' (filtered to ' . ($this->selectedVehicleType === 'private_car' ? 'Private Car' : 'Shared Microbus') . ')'
            : '';

        $body = "Locked {$bookings->count()} bookings{$filterNote} and queued {$queuedCount} WhatsApp messages for delivery.";

        Notification::make()->success()->title('Distribution Ensured')->body($body)->send();

        $this->dispatch('refresh-map');
    }

    public function ensureAndNotifyForVehicle(int $vehicleId): void
    {
        if (!$this->selectedDate) {
            Notification::make()->warning()->title('Please select a date first.')->send();
            return;
        }

        $greenApi = app(GreenApiService::class);
        if (!$greenApi->isAuthorized()) {
            Notification::make()->danger()->title('WhatsApp not connected')
                ->body('The Green API instance is not authorized. Use "Check WhatsApp Status" for details.')
                ->send();
            return;
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            Notification::make()->danger()->title('Vehicle not found')->send();
            return;
        }

        $bookings = Booking::with(['client', 'tour', 'departure', 'outboundVehicle', 'returnVehicle'])
            ->where('booking_date', $this->selectedDate)
            ->where('is_distribution_ensured', false)
            ->whereNotNull('outbound_vehicle_id')
            ->where(function ($q) {
                $q->where('trip_type', 'one_way')
                  ->orWhereNotNull('return_vehicle_id');
            })
            ->where(function ($q) use ($vehicleId) {
                $q->where('outbound_vehicle_id', $vehicleId)
                  ->orWhere('return_vehicle_id', $vehicleId);
            })
            ->get();

        if ($bookings->isEmpty()) {
            Notification::make()->warning()
                ->title('Nothing to notify')
                ->body("No complete, unlocked bookings are assigned to {$vehicle->plate_number} for this date.")
                ->send();
            return;
        }

        $queuedCount = $this->queueNotificationsForBookings($bookings);

        $body = "Locked {$bookings->count()} booking(s) for {$vehicle->plate_number} and queued {$queuedCount} WhatsApp message(s) for delivery.";

        Notification::make()->success()->title('Vehicle Distribution Ensured')->body($body)->send();

        $this->dispatch('refresh-map');
    }

    public function ensureVehicleAction(): Action
    {
        return Action::make('ensureVehicle')
            ->label('Ensure & Notify')
            ->icon('heroicon-s-check-badge')
            ->color('success')
            ->size(ActionSize::Small)
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => 'Lock & Notify Bookings for ' . ($arguments['plate'] ?? 'this Vehicle'))
            ->modalDescription('This will lock every fully assigned, unlocked booking for this vehicle on the selected date and queue a WhatsApp confirmation for each client.')
            ->modalSubmitActionLabel('Yes, lock & notify')
            ->action(function (array $arguments) {
                $this->ensureAndNotifyForVehicle((int) $arguments['vehicleId']);
            });
    }
}