<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'is_cash_on_board_paid' => 'boolean',
         
            // Each direction is locked on its own.
            'is_outbound_ensured' => 'boolean',
            'is_return_ensured' => 'boolean',
        ];
    }

    public function scopeUnassigned(Builder $query): void
    {
        $query->where(function ($q) {
            $q->whereNull('outbound_vehicle_id')
              ->orWhere(function ($sub) {
                  $sub->where('trip_type', 'round_trip')->whereNull('return_vehicle_id');
              });
        });
    }

    public function scopeFullyAssigned(Builder $query): void
    {
        $query->whereNotNull('outbound_vehicle_id')
              ->where(function ($q) {
                  $q->where('trip_type', 'one_way')
                    ->orWhereNotNull('return_vehicle_id');
              });
    }

    public function scopeForVehicle(Builder $query, int $id): void
    {
        $query->where(function ($q) use ($id) {
            $q->where('outbound_vehicle_id', $id)
              ->orWhere('return_vehicle_id', $id);
        });
    }

    /**
     * True when at least one direction is locked.
     */
    public function hasLockedLeg(): bool
    {
        return (bool) ($this->is_outbound_ensured || $this->is_return_ensured);
    }

    protected static function booted(): void
    {
        static::updating(function (Booking $b) {
            $wasOutLocked = (bool) $b->getOriginal('is_outbound_ensured');
            $wasRetLocked = (bool) $b->getOriginal('is_return_ensured');

            if (! $wasOutLocked && ! $wasRetLocked) {
                return;
            }

            $unlockingOut = $wasOutLocked && ! $b->is_outbound_ensured;
            $unlockingRet = $wasRetLocked && ! $b->is_return_ensured;

            if ($unlockingOut) $b->outbound_vehicle_id = null;
            if ($unlockingRet) $b->return_vehicle_id = null;

            $allowed = [
                'is_cash_on_board_paid', 'payment_status', 'whatsapp_status',
                'is_outbound_ensured', 'is_return_ensured', 'updated_at',
            ];

            if (! $wasOutLocked || $unlockingOut) $allowed[] = 'outbound_vehicle_id';
            if (! $wasRetLocked || $unlockingRet) $allowed[] = 'return_vehicle_id';

            if (array_diff(array_keys($b->getDirty()), $allowed)) {
                throw new \DomainException('This booking is locked.');
            }
        });

        static::deleting(function (Booking $b) {
            if ($b->hasLockedLeg()) {
                throw new \DomainException('This booking is locked.');
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tour(): BelongsTo
    {
        return $this->belongsTo(Tour::class)->withTrashed();
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(TourDeparture::class, 'tour_departure_id')->withTrashed();
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(TourPricingTier::class, 'tour_pricing_tier_id')->withTrashed();
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function outboundVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'outbound_vehicle_id');
    }

    public function returnVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'return_vehicle_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}