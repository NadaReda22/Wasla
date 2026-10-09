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
            // Added the "New Booking" button here
            Actions\CreateAction::make(),
            
            Actions\Action::make('distribution')
                ->label(fn () => __('admin.actions.fleet_distribution') ?? 'Fleet Distribution')
                ->icon('heroicon-s-map')
                ->color('warning')
                ->url(fn () => static::getResource()::getUrl('distribution')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(fn () => __('admin.tabs.all_bookings') ?? 'All Bookings')
                ->icon('heroicon-m-queue-list'),
                
            'unassigned' => Tab::make(fn () => __('admin.tabs.needs_assignment') ?? 'Needs Assignment')
                ->icon('heroicon-m-exclamation-triangle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) {
                    $q->whereNull('outbound_vehicle_id')
                      ->orWhere(function ($sub) {
                          $sub->where('trip_type', 'round_trip')->whereNull('return_vehicle_id');
                      });
                })),
                
            'assigned' => Tab::make(fn () => __('admin.tabs.fully_assigned') ?? 'Fully Assigned')
                ->icon('heroicon-m-check-badge')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('outbound_vehicle_id')
                      ->where(function ($q) {
                          $q->where('trip_type', 'one_way')
                            ->orWhereNotNull('return_vehicle_id');
                      })),
        ];
    }
}