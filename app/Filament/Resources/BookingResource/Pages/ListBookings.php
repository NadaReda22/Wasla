<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('distribution')
                ->label(fn () => __('admin.actions.fleet_distribution'))
                ->icon('heroicon-s-map')
                ->color('warning')
                ->url(fn () => static::getResource()::getUrl('distribution')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.tabs.all_bookings'))->icon('heroicon-m-queue-list'),
            'unassigned' => Tab::make(__('admin.tabs.needs_assignment'))
                ->icon('heroicon-m-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->unassigned()),
            'assigned' => Tab::make(__('admin.tabs.fully_assigned'))
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->fullyAssigned()),
        ];
    }
}