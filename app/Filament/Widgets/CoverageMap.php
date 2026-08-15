<?php
namespace App\Filament\Widgets;

use App\Models\Area;
use App\Models\Setting;
use Filament\Widgets\Widget;

class CoverageMap extends Widget
{
    protected static string $view = 'filament.widgets.coverage-map';

    public $areas;
    public $isHidden;

    public function mount(): void
    {
        $settings = Setting::first();
        $this->isHidden = $settings?->hide_coverage_area ?? false;
        $this->areas = Area::where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        // Dispatch an event to tell the frontend to initialize the map
        $this->dispatch('init-coverage-map', areas: $this->areas->toArray());
    }

    public function getColumnSpan(): int|array
    {
        return 12;
    }

    public static function canView(): bool
    {
        $settings = Setting::first();
        return !($settings?->hide_coverage_area ?? false);
    }
}