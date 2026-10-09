<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OperationsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    public static function getNavigationLabel(): string
    {
        return __('admin.nav.dashboard') ?? 'Dashboard';
    }
    
    public function getTitle(): string
    {
        return __('admin.nav.dashboard') ?? 'Dashboard';
    }
    
    protected static ?string $navigationGroup = null;
    
    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.operations-dashboard';
    
    protected ?string $heading = '';

    protected function getViewData(): array
    {
        $totalProfitsAllTime = Booking::sum('total_price');
        $totalProfitsToday = Booking::whereDate('created_at', Carbon::today())->sum('total_price');

        $totalSeats = Booking::sum('seats_count');
        $uniqueDays = Booking::distinct('booking_date')->count('booking_date');
        $avgSeatsPerDay = $uniqueDays > 0 ? round($totalSeats / $uniqueDays, 1) : 0;

        $totalBookings = Booking::count();
        $totalClients = Client::count();
        $pendingCash = Booking::where('is_cash_on_board_paid', false)->sum('cash_due_on_board');

        // Chart 1
        $last7Days = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));
        // Use translated format for days (e.g., Mon/الاثنين)
        $chartLabels = $last7Days->map(fn($d) => $d->translatedFormat('D'))->toArray(); 
        
        $bookingsPerDay = [];
        $revenuePerDay = [];
        
        foreach ($last7Days as $day) {
            $dayBookings = Booking::whereDate('created_at', $day)->get();
            $bookingsPerDay[] = $dayBookings->count();
            $revenuePerDay[] = $dayBookings->sum('total_price');
        }

        // Chart 2
        $topTours = Booking::select('tour_id', DB::raw('count(*) as total'))
            ->with('tour')
            ->groupBy('tour_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();
            
        $topTourLabels = $topTours->map(function($b) {
            return $b->tour ? (app()->getLocale() === 'ar' ? ($b->tour->name_ar ?? $b->tour->name_en) : $b->tour->name_en) : (__('admin.options.custom_tour') ?? 'Custom Tour');
        })->toArray();
        $topTourData = $topTours->pluck('total')->toArray();

        return [
            'totalProfitsAllTime' => $totalProfitsAllTime,
            'totalProfitsToday'   => $totalProfitsToday,
            'avgSeatsPerDay'      => $avgSeatsPerDay,
            'totalBookings'       => $totalBookings,
            'totalClients'        => $totalClients,
            'pendingCash'         => $pendingCash,
            'chartLabels'         => $chartLabels,
            'bookingsPerDay'      => $bookingsPerDay,
            'revenuePerDay'       => $revenuePerDay,
            'topTourLabels'       => $topTourLabels,
            'topTourData'         => $topTourData,
        ];
    }
}