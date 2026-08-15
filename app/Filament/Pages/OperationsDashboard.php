<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class OperationsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?string $navigationGroup = null;
    
    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.operations-dashboard';
    
    protected ?string $heading = '';
}