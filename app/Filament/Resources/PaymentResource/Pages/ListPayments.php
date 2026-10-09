<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        // Removed CreateAction to enforce read-only
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Transactions')
                ->icon('heroicon-m-queue-list'),
                
            'paid' => Tab::make('Paid')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid')),
                
            'pending' => Tab::make('Pending')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
                
            'failed' => Tab::make('Failed')
                ->icon('heroicon-m-x-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'failed')),
        ];
    }
}