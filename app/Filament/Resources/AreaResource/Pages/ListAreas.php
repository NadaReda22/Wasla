<?php
namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\CoverageMap;

class ListAreas extends ListRecords
{
    protected static string $resource = AreaResource::class;

    public function toggleCoverageVisibility(): void
    {
        $settings = Setting::firstOrCreate([]);
        $newState = !$settings->hide_coverage_area;
        $settings->update(['hide_coverage_area' => $newState]);

        Notification::make()
            ->title($newState ? 'Coverage section hidden' : 'Coverage section visible')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        // Get fresh state for the button label
        $settings = Setting::first();
        $isHidden = $settings?->hide_coverage_area ?? false;

        return [
            Actions\Action::make('toggleCoverage')
                ->label($isHidden ? 'Show Coverage Section' : 'Hide Coverage Section')
                ->icon($isHidden ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                ->color($isHidden ? 'success' : 'gray')
                ->action('toggleCoverageVisibility'),

            Actions\CreateAction::make()
                ->label('Add Area')
                ->icon('heroicon-o-plus'),
        ];
    }

     protected function getFooterWidgets(): array
    {
        return [
            CoverageMap::class,
        ];
    }
}