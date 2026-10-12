<?php

namespace App\Services;

use App\Models\TourPricingTier;
use Filament\Notifications\Notification;

class PricingCalculatorService
{
    /**
     * @return array{tier_id: ?int, total_price: float|int, deposit_amount: float|int}
     */
    public function calculate(?int $tourId, ?string $tripType, ?string $vehicleType, int $pax): array
    {
        if (! $tourId || ! $tripType || ! $vehicleType || $pax < 1) {
            return $this->emptyState();
        }

        $tiers = TourPricingTier::where('tour_id', $tourId)
            ->where('is_active', true)
            ->where('trip_type', $tripType)
            ->where('vehicle_type', $vehicleType)
            ->orderBy('pax_count', 'desc')
            ->get();

        $baseTier = $tiers->where('is_base', true)->first();
        $offers = $tiers->where('is_base', false);

        if (! $baseTier && $offers->isEmpty()) {
            Notification::make()->warning()->title('No pricing configured for these options.')->send();
            return $this->emptyState();
        }

        $totalPrice = 0;
        $depositDue = 0;
        $remainingPax = $pax;
        $primaryTierId = null;

        if ($vehicleType === 'private') {
            $carTier = $offers->first() ?? $baseTier;
            $capacity = $carTier->pax_count > 0 ? $carTier->pax_count : 4;
            $carsNeeded = (int) ceil($remainingPax / $capacity);

            $totalPrice = $carsNeeded * $carTier->total_price;
            $depositDue = $carsNeeded * $carTier->deposit_amount;
            $primaryTierId = $carTier->id;
        } else {
            foreach ($offers as $offer) {
                if ($remainingPax <= 0) {
                    break;
                }
                if ($offer->pax_count <= 0) {
                    continue;
                }

                $multiplier = (int) floor($remainingPax / $offer->pax_count);
                if ($multiplier <= 0) {
                    continue;
                }

                $primaryTierId ??= $offer->id;

                $totalPrice += $offer->total_price * $multiplier;
                $depositDue += $offer->deposit_amount * $multiplier;
                $remainingPax -= $offer->pax_count * $multiplier;
            }

            if ($remainingPax > 0 && $baseTier) {
                $primaryTierId ??= $baseTier->id;
                $totalPrice += $remainingPax * $baseTier->total_price;
                $depositDue += $remainingPax * $baseTier->deposit_amount;
            }
        }

        return [
            'tier_id' => $primaryTierId,
            'total_price' => $totalPrice,
            'deposit_amount' => $depositDue,
        ];
    }

    private function emptyState(): array
    {
        return [
            'tier_id' => null,
            'total_price' => 0,
            'deposit_amount' => 0,
        ];
    }
}