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
use App\Services\Messaging\BookingWhatsAppMessage;

class BookingDistribution extends Page implements HasActions
{
    use InteractsWithActions;

    /** Only bookings with this payment status can be distributed, locked or notified. */
    private const PAID_STATUS = 'paid';

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

        // The tour list depends on the date: drop the selected tour if it has no bookings on the new date.
        if ($this->selectedTour !== '' && !array_key_exists($this->selectedTour, $this->getAvailableToursProperty())) {
            $this->selectedTour = '';
        }
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

    /**
     * Only the tours that have paid bookings on the selected date.
     */
    public function getAvailableToursProperty()
    {
        if (!$this->selectedDate) return [];

        $tourIds = Booking::where('booking_date', $this->selectedDate)
            ->where('payment_status', self::PAID_STATUS)
            ->whereNotNull('tour_id')
            ->distinct()
            ->pluck('tour_id');

        return Tour::withTrashed()
            ->whereIn('id', $tourIds)
            ->get()
            ->mapWithKeys(fn($t) => [$t->id => $t->name_ru ? "{$t->name_en} / {$t->name_ru}" : $t->name_en])
            ->toArray();
    }

    public function getAvailableDepartureTimesProperty()
    {
        if (!$this->selectedDate) return [];

        $query = Booking::where('booking_date', $this->selectedDate)
            ->where('payment_status', self::PAID_STATUS)
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
            ->where('payment_status', self::PAID_STATUS)
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
            ->where('payment_status', self::PAID_STATUS)
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
            ->where('booking_date', $this->selectedDate)
            ->where('payment_status', self::PAID_STATUS);

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
                    'booking_ids' => $tBookings->where('is_outbound_ensured', false)->pluck('id')->values()->toArray(),
                    'is_locked' => $tBookings->every(fn ($b) => (bool) $b->is_outbound_ensured),
                    'locked_count' => $tBookings->where('is_outbound_ensured', true)->count(),
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
                    'booking_ids' => $tBookings->where('is_return_ensured', false)->pluck('id')->values()->toArray(),
                    'is_locked' => $tBookings->every(fn ($b) => (bool) $b->is_return_ensured),
                    'locked_count' => $tBookings->where('is_return_ensured', true)->count(),
                    'zones' => $tBookings->pluck('area.name_en')->filter()->unique()->values()->toArray(),
                ];
            }

            $vehicle->assignments_list = $assignments;

            $paidToday = $allTodayBookings->where('payment_status', self::PAID_STATUS);

            $vehicle->pending_ensure_count =
                $paidToday->where('outbound_vehicle_id', $vehicle->id)->where('is_outbound_ensured', false)->count()
                + $paidToday->where('trip_type', 'round_trip')->where('return_vehicle_id', $vehicle->id)->where('is_return_ensured', false)->count();

            $vehicle->locked_count =
                $allTodayBookings->where('outbound_vehicle_id', $vehicle->id)->where('is_outbound_ensured', true)->count()
                + $allTodayBookings->where('return_vehicle_id', $vehicle->id)->where('is_return_ensured', true)->count();

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
            // Group by tour and departure time to find the maximum seats taken for this vehicle in the current direction.
            $groupedByTime = $currentDirectionBookings->groupBy(function($b) use ($direction) {
                if ($direction === 'outbound') {
                    return $b->tour_id . '|' . ($b->departure?->departure_time ?? '09:00:00');
                } else {
                    return $b->tour_id . '|' . ($b->departure?->return_time ?? '00:00:00');
                }
            });

            $maxSeats = 0;
            foreach ($groupedByTime as $group) {
                $sum = $group->sum('seats_count');
                if ($sum > $maxSeats) {
                    $maxSeats = $sum;
                }
            }
            $vehicle->seats_taken = (int) $maxSeats;

            return $vehicle;
        })->values();
    }

    public function assignToVehicle($vehicleId)
    {
        if (empty($this->selectedBookings)) {
            Notification::make()->warning()->title(__('admin.notifications.select_bookings_first'))->send();
            return;
        }

        $lockedColumn = $this->direction === 'outbound' ? 'is_outbound_ensured' : 'is_return_ensured';

        $ensuredBookings = Booking::whereIn('id', $this->selectedBookings)
            ->where($lockedColumn, true)
            ->count();

        if ($ensuredBookings > 0) {
            Notification::make()->danger()
                ->title(__('admin.booking.notifications.action_forbidden'))
                ->body(__('admin.fleet.cannot_reassign_locked'))
                ->send();
            return;
        }

        $unpaid = Booking::whereIn('id', $this->selectedBookings)
            ->where('payment_status', '!=', self::PAID_STATUS)
            ->count();

        if ($unpaid > 0) {
            Notification::make()->danger()
                ->title(__('admin.fleet.unpaid_selected_title'))
                ->body(__('admin.fleet.unpaid_selected_body'))
                ->send();
            $this->selectedBookings = [];
            return;
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle || !$vehicle->is_active) {
            Notification::make()->danger()->title(__('admin.notifications.invalid_vehicle'))->send();
            return;
        }

        $columnToUpdate = $this->direction === 'outbound' ? 'outbound_vehicle_id' : 'return_vehicle_id';
        
        $selectedBookingsData = Booking::with('departure')->whereIn('id', $this->selectedBookings)->get();
        
        $groupedSelected = $selectedBookingsData->groupBy(function($b) {
             return $b->tour_id . '|' . ($b->tour_departure_id ?? 'default');
        });

        foreach ($groupedSelected as $groupKey => $newBookings) {
            $seatsNeededForThisTime = $newBookings->sum('seats_count');
            $firstBooking = $newBookings->first();
            
            $existingSeatsAtThisTime = Booking::where('booking_date', $this->selectedDate)
                ->where($columnToUpdate, $vehicleId)
                ->where('tour_id', $firstBooking->tour_id)
                ->where('tour_departure_id', $firstBooking->tour_departure_id)
                ->sum('seats_count');

            if (($existingSeatsAtThisTime + $seatsNeededForThisTime) > $vehicle->capacity) {
                
                $timeStr = $this->direction === 'outbound' 
                           ? ($firstBooking->departure?->departure_time ? Carbon::parse($firstBooking->departure->departure_time)->format('g:i A') : '09:00 AM')
                           : ($firstBooking->departure?->return_time ? Carbon::parse($firstBooking->departure->return_time)->format('g:i A') : 'TBD');
                           
                Notification::make()->danger()
                    ->title(__('admin.notifications.not_enough_capacity'))
                    ->body(__('admin.notifications.capacity_exceeded_slot_body', [
                        'time' => $timeStr,
                        'existing' => $existingSeatsAtThisTime,
                        'needed' => $seatsNeededForThisTime,
                        'max' => $vehicle->capacity,
                    ]))->send();
                return;
            }
        }

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
        $lockedColumn = $direction === 'outbound' ? 'is_outbound_ensured' : 'is_return_ensured';

        $ensuredBookings = Booking::whereIn('id', $bookingIds)
            ->where($lockedColumn, true)
            ->count();

        if ($ensuredBookings > 0) {
            Notification::make()->danger()
                ->title(__('admin.booking.notifications.action_forbidden'))
                ->body(__('admin.fleet.cannot_unassign_locked'))
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

    private function pendingLegsQuery()
    {
        return Booking::with(['client', 'tour', 'departure', 'outboundVehicle', 'returnVehicle', 'area'])
            ->where('booking_date', $this->selectedDate)
            ->where('payment_status', self::PAID_STATUS)
            ->where(function ($q) {
                $q->where(fn ($s) => $s->whereNotNull('outbound_vehicle_id')->where('is_outbound_ensured', false))
                  ->orWhere(fn ($s) => $s->where('trip_type', 'round_trip')->whereNotNull('return_vehicle_id')->where('is_return_ensured', false));
            });
    }

    private function legsToLock(Booking $b, ?int $vehicleId = null): array
    {
        $typeOk = fn ($vehicle) => !$this->selectedVehicleType || $vehicle?->type === $this->selectedVehicleType;
        $legs = [];

        if ($b->outbound_vehicle_id
            && !$b->is_outbound_ensured
            && ($vehicleId === null || (int) $b->outbound_vehicle_id === $vehicleId)
            && $typeOk($b->outboundVehicle)) {
            $legs[] = 'outbound';
        }

        if ($b->trip_type === 'round_trip'
            && $b->return_vehicle_id
            && !$b->is_return_ensured
            && ($vehicleId === null || (int) $b->return_vehicle_id === $vehicleId)
            && $typeOk($b->returnVehicle)) {
            $legs[] = 'return';
        }

        return $legs;
    }

    private function lockAndNotifyLegs(Collection $bookings, ?int $vehicleId = null): array
    {
        $handled = 0;
        $queued = 0;

        foreach ($bookings as $record) {
            $legs = $this->legsToLock($record, $vehicleId);

            if (empty($legs)) {
                continue;
            }

            $handled++;

            $lock = [];
            if (in_array('outbound', $legs, true)) {
                $lock['is_outbound_ensured'] = true;
            }
            if (in_array('return', $legs, true)) {
                $lock['is_return_ensured'] = true;
            }

            $digits = preg_replace('/[^0-9]/', '', (string) $record->client?->whatsapp_number);

            if ($digits === '') {
                $record->update($lock + ['whatsapp_status' => 'failed']);
                continue;
            }

            $record->update($lock + ['whatsapp_status' => 'pending']);

            SendWhatsAppMessageJob::dispatch(
                $record->id,
                $digits . '@c.us',
                BookingWhatsAppMessage::clientText($record, $legs)
            );

            $queued++;
        }

        return [$handled, $queued];
    }

    private function whatsappReadyOrNotify(): bool
    {
        $greenApi = app(GreenApiService::class);

        if (!$greenApi->isEnabled() || !$greenApi->isConfigured()) {
            Notification::make()->danger()
                ->title(__('admin.fleet.whatsapp_not_set_up_title'))
                ->body(__('admin.fleet.whatsapp_not_set_up_body'))
                ->send();
            return false;
        }

        if (!$greenApi->isAuthorized()) {
            Notification::make()->danger()
                ->title(__('admin.fleet.whatsapp_not_connected_title'))
                ->body(__('admin.fleet.whatsapp_not_connected_body'))
                ->send();
            return false;
        }

        return true;
    }

    public function ensureAndNotifyDailyAction(): Action
    {
        return Action::make('ensureAndNotifyDaily')
            ->label(__('admin.fleet.ensure_notify_all'))
            ->icon('heroicon-s-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('admin.fleet.confirm_heading'))
            ->modalDescription(__('admin.fleet.confirm_body'))
            ->modalSubmitActionLabel(__('admin.fleet.confirm_submit'))
            ->action(fn () => $this->ensureAndNotifyDaily());
    }

    public function checkGreenApiStatusAction(): Action
    {
        return Action::make('checkGreenApiStatus')
            ->label(__('admin.fleet.check_whatsapp_status'))
            ->icon('heroicon-o-signal')
            ->color('gray')
            ->action(function () {
                $service = app(GreenApiService::class);

                if (!$service->isEnabled()) {
                    Notification::make()->warning()
                        ->title(__('admin.fleet.whatsapp_disabled_title'))
                        ->body(__('admin.fleet.whatsapp_disabled_body'))
                        ->send();
                    return;
                }

                if (!$service->isConfigured()) {
                    Notification::make()->danger()
                        ->title(__('admin.fleet.green_not_configured_title'))
                        ->body(__('admin.fleet.green_not_configured_body'))
                        ->send();
                    return;
                }

                $state = $service->getInstanceState();

                match ($state) {
                    'authorized' => Notification::make()->success()->title(__('admin.fleet.state_authorized_title'))->body(__('admin.fleet.state_authorized_body'))->send(),
                    'notAuthorized' => Notification::make()->danger()->title(__('admin.fleet.state_not_authorized_title'))->body(__('admin.fleet.state_not_authorized_body'))->send(),
                    'blocked' => Notification::make()->danger()->title(__('admin.fleet.state_blocked_title'))->body(__('admin.fleet.state_blocked_body'))->send(),
                    default => Notification::make()->warning()->title(__('admin.fleet.state_unknown_title'))->body(__('admin.fleet.state_unknown_body', ['state' => $state ?? __('admin.fleet.unreachable')]))->send(),
                };
            });
    }

    public function ensureAndNotifyDaily()
    {
        if (!$this->selectedDate) {
            Notification::make()->warning()->title(__('admin.fleet.select_date_first'))->send();
            return;
        }

        if (!$this->whatsappReadyOrNotify()) {
            return;
        }

        [$handled, $queuedCount] = $this->lockAndNotifyLegs($this->pendingLegsQuery()->get());

        if ($handled === 0) {
            Notification::make()->warning()
                ->title(__('admin.fleet.no_pending_title'))
                ->body(__('admin.fleet.no_pending_body'))
                ->send();
            return;
        }

        $filterNote = '';
        if ($this->selectedVehicleType) {
            $vehicleTypeName = $this->selectedVehicleType === 'private_car' ? __('admin.options.private_car') : __('admin.options.shared_microbus');
            $filterNote = __('admin.fleet.filtered_to', ['type' => $vehicleTypeName]);
        }

        $body = __('admin.fleet.ensured_body', [
            'count' => $handled,
            'note' => $filterNote,
            'queued' => $queuedCount
        ]);

        Notification::make()->success()->title(__('admin.fleet.ensured_title'))->body($body)->send();

        $this->dispatch('refresh-map');
    }

    public function ensureAndNotifyForVehicle(int $vehicleId): void
    {
        if (!$this->selectedDate) {
            Notification::make()->warning()->title(__('admin.fleet.select_date_first'))->send();
            return;
        }

        if (!$this->whatsappReadyOrNotify()) {
            return;
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            Notification::make()->danger()->title(__('admin.fleet.vehicle_not_found'))->send();
            return;
        }

        $bookings = $this->pendingLegsQuery()
            ->where(function ($q) use ($vehicleId) {
                $q->where('outbound_vehicle_id', $vehicleId)
                  ->orWhere('return_vehicle_id', $vehicleId);
            })
            ->get();

        [$handled, $queuedCount] = $this->lockAndNotifyLegs($bookings, $vehicleId);

        if ($handled === 0) {
            Notification::make()->warning()
                ->title(__('admin.fleet.nothing_to_notify_title'))
                ->body(__('admin.fleet.nothing_to_notify_body', ['plate' => $vehicle->plate_number]))
                ->send();
            return;
        }

        $body = __('admin.fleet.vehicle_ensured_body', [
            'count' => $handled,
            'plate' => $vehicle->plate_number,
            'queued' => $queuedCount
        ]);

        Notification::make()->success()->title(__('admin.fleet.vehicle_ensured_title'))->body($body)->send();

        $this->dispatch('refresh-map');
    }

    public function ensureVehicleAction(): Action
    {
        return Action::make('ensureVehicle')
            ->label(__('admin.fleet.ensure_notify_vehicle'))
            ->icon('heroicon-s-check-badge')
            ->color('success')
            ->size(ActionSize::Small)
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => __('admin.fleet.vehicle_modal_heading', ['plate' => $arguments['plate'] ?? __('admin.fleet.this_vehicle')]))
            ->modalDescription(__('admin.fleet.vehicle_modal_body'))
            ->modalSubmitActionLabel(__('admin.fleet.confirm_submit'))
            ->action(function (array $arguments) {
                $this->ensureAndNotifyForVehicle((int) $arguments['vehicleId']);
            });
    }
}