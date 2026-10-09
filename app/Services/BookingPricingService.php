<?php
namespace App\Services;

use App\Models\TourPricingTier;
use Filament\Notifications\Notification;

class BookingPricingService
{
    public function calculatePricing(?int $tourId, ?string $tripType, ?string $vehicleType, int $pax): array
    {
        if (!$tourId || !$tripType || !$vehicleType || $pax < 1) {
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

        if (!$baseTier && $offers->isEmpty()) {
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
                if ($remainingPax <= 0) break;
                if ($offer->pax_count <= 0) continue;

                $multiplier = (int) floor($remainingPax / $offer->pax_count);
                if ($multiplier <= 0) continue;

                if (!$primaryTierId) $primaryTierId = $offer->id;

                $totalPrice += $offer->total_price * $multiplier;
                $depositDue += $offer->deposit_amount * $multiplier;
                $remainingPax -= $offer->pax_count * $multiplier;
            }

            if ($remainingPax > 0 && $baseTier) {
                if (!$primaryTierId) $primaryTierId = $baseTier->id;
                $totalPrice += $remainingPax * $baseTier->total_price;
                $depositDue += $remainingPax * $baseTier->deposit_amount;
            }
        }

        return [
            'tour_pricing_tier_id' => $primaryTierId,
            'total_price' => $totalPrice,
            'deposit_paid' => $depositDue,
            'cash_due_on_board' => max(0, $totalPrice - $depositDue),
        ];
    }

    private function emptyState(): array
    {
        return [
            'tour_pricing_tier_id' => null,
            'total_price' => 0,
            'deposit_paid' => 0,
            'cash_due_on_board' => 0,
        ];
    }
}