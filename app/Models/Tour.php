<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Tour extends Model
{
    use Sluggable, SoftDeletes;

    protected $fillable = [
        'slug', 'name_en', 'name_ar', 'name_ru', 'image_url',
        'location', 'tag_name', 'tag_color_class',
        'description_en', 'description_ar', 'description_ru', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'base_seats' => 'integer',
        ];
    }

    public function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name_ru ? "{$this->name_en} / {$this->name_ru}" : $this->name_en
        );
    }

    public function getAvailableDates(int $daysAhead = 4): array
    {
        $today = Carbon::today();$dates = [];

        if ($this->departures->isEmpty()) {
            for ($i = 0; $i <=$daysAhead; $i++) {$target = $today->copy()->addDays($i);
                $targetDateTime = Carbon::parse($target->toDateString() . ' 09:00:00');
                
                if ($targetDateTime->isFuture()) {
                    $iso =$target->toDateString();
                    $dates[$iso] = $i === 0 ? 'Today (' .$target->format('M j') . ')' : 
                                  ($i === 1 ? 'Tomorrow (' . $target->format('M j') . ')' :$target->format('D, M j, Y'));
                }
            }
        } else {
            foreach ($this->departures->where('is_active', true) as$dep) {
                $timeOnly = Carbon::parse($dep->departure_time)->format('H:i:s');
                for ($i = 0; $i <= $daysAhead; $i++) {
                    $target =$today->copy()->addDays($i);$targetDateTime = Carbon::parse($target->toDateString() . ' ' .$timeOnly);
                    
                    if ($targetDateTime->isFuture()) {
                        $iso =$target->toDateString();
                        $dates[$iso] = $i === 0 ? 'Today (' .$target->format('M j') . ')' : 
                                      ($i === 1 ? 'Tomorrow (' . $target->format('M j') . ')' :$target->format('D, M j, Y'));
                    }
                }
            }
        }

        ksort($dates);
        return $dates;
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name_en'
            ]
        ];
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function pricingTiers()
    {
        return $this->hasMany(TourPricingTier::class);
    }

    public function departures()
    {
        return $this->hasMany(TourDeparture::class)->orderBy('departure_time');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}