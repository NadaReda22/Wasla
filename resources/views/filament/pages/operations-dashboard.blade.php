<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-screen-2xl mx-auto space-y-8" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        
        <!-- ==================== 1. PAGE HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#0A2540]">{{ __('admin.nav.dashboard') ?? 'Operations Dashboard' }}</h1>
                <p class="text-gray-500 text-sm mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, j F Y') }} · {{ __('admin.dashboard.sharm_el_sheikh') ?? 'Sharm El-Sheikh' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ \App\Filament\Resources\BookingResource::getUrl('index') }}" class="bg-[#1877C9] text-white px-4 py-2 text-sm font-semibold rounded-lg hover:bg-[#1460a8] transition transform active:scale-95 inline-flex items-center">
                    + {{ __('admin.nav.bookings') ?? 'Bookings' }}
                </a>
            </div>
        </div>

        <!-- ==================== 2. KPI GRID (6 METRICS) ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            
            <!-- 1. All Time Profits -->
            <div class="bg-white rounded-2xl border-2 border-[#16a34a]/20 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-[#16a34a]/10 rounded-xl flex items-center justify-center text-[#16a34a]">
                        <x-heroicon-o-banknotes class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#16a34a] mb-1 tabular-nums">${{ number_format($totalProfitsAllTime, 2) }}</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">{{ __('admin.dashboard.total_profits_all_time') ?? 'Total Profits (All Time)' }}</div>
                <div class="text-xs text-gray-500">{{ __('admin.dashboard.total_gross_revenue') ?? 'Total gross revenue' }}</div>
            </div>

            <!-- 2. Today's Profits -->
            <div class="bg-white rounded-2xl border-2 border-[#1877C9]/20 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-[#1877C9]/10 rounded-xl flex items-center justify-center text-[#1877C9]">
                        <x-heroicon-o-currency-dollar class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1877C9] mb-1 tabular-nums">${{ number_format($totalProfitsToday, 2) }}</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">{{ __('admin.dashboard.todays_revenue') ?? 'Today\'s Profits' }}</div>
                <div class="text-xs text-gray-500">{{ __('admin.dashboard.sales_generated_today') ?? 'Sales generated today' }}</div>
            </div>

            <!-- 3. Pending Cash -->
            <div class="bg-white rounded-2xl border-2 border-amber-300 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <x-heroicon-o-hand-raised class="w-5 h-5" />
                    </div>
                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ __('admin.dashboard.collectable') ?? 'Collectable' }}</span>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 mb-1 tabular-nums">${{ number_format($pendingCash, 2) }}</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">{{ __('admin.dashboard.pending_cash') ?? 'Pending Cash on Board' }}</div>
                <div class="text-xs text-gray-500">{{ __('admin.dashboard.requires_driver_collection') ?? 'Requires driver collection' }}</div>
            </div>

            <!-- 4. Total Bookings -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600">
                        <x-heroicon-o-ticket class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#0A2540] mb-1 tabular-nums">{{ number_format($totalBookings) }}</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">{{ __('admin.columns.total_bookings') ?? 'Total Bookings' }}</div>
                <div class="text-xs text-gray-500">{{ __('admin.dashboard.successful_reservations') ?? 'Successful reservations' }}</div>
            </div>

            <!-- 5. Total Clients -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600">
                        <x-heroicon-o-users class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#0A2540] mb-1 tabular-nums">{{ number_format($totalClients) }}</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">{{ __('admin.nav.clients') ?? 'Total Clients' }}</div>
                <div class="text-xs text-gray-500">{{ __('admin.dashboard.unique_customers') ?? 'Unique registered customers' }}</div>
            </div>

            <!-- 6. Avg Seats Per Day -->
            <div class="bg-white rounded-2xl border-2 border-indigo-200 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-500">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-indigo-600 mb-1 tabular-nums">{{ $avgSeatsPerDay }}</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">{{ __('admin.dashboard.avg_seats_per_day') ?? 'Average Seats / Day' }}</div>
                <div class="text-xs text-gray-500">{{ __('admin.dashboard.across_operational_days') ?? 'Across all active operational days' }}</div>
            </div>

        </div>

        <!-- ==================== 3. CHARTS ROW ==================== -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6" x-data="dashboardCharts()" x-init="initCharts()">
            
            <!-- Bar Chart (Last 7 Days Revenue & Bookings) -->
            <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-[#0A2540] text-base">{{ __('admin.dashboard.sales_performance') ?? 'Sales Performance' }}</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ __('admin.dashboard.last_7_days') ?? 'Last 7 Days' }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 bg-[#1877C9] rounded-sm"></div>
                            <span class="text-gray-500">{{ __('admin.dashboard.bookings_count') ?? 'Bookings Count' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 bg-[#16a34a] rounded-sm"></div>
                            <span class="text-gray-500">{{ __('admin.dashboard.revenue_usd') ?? 'Revenue ($)' }}</span>
                        </div>
                    </div>
                </div>
                <div class="relative h-[240px] w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart (Top Tours) -->
            <div class="xl:col-span-1 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col">
                <div class="mb-4">
                    <h3 class="font-bold text-[#0A2540] text-base">{{ __('admin.dashboard.top_performing_tours') ?? 'Top Performing Tours' }}</h3>
                    <p class="text-gray-500 text-xs mt-0.5">{{ __('admin.dashboard.by_total_booking_count') ?? 'By total booking count' }}</p>
                </div>
                <div class="flex-1 flex flex-col items-center justify-center">
                    <div class="relative h-[200px] w-full flex justify-center">
                        <canvas id="topToursChart"></canvas>
                    </div>
                    
                    @if(empty($topTourLabels))
                        <div class="text-sm text-gray-400 mt-4">{{ __('admin.dashboard.no_tour_data') ?? 'No tour data yet.' }}</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCharts', () => ({
                initCharts() {
                    const chartLabels = @json($chartLabels);
                    const bookingsData = @json($bookingsPerDay);
                    const revenueData = @json($revenuePerDay);
                    
                    const topTourLabels = @json($topTourLabels);
                    const topTourData = @json($topTourData);
                    
                    const labelBookings = @json(__('admin.nav.bookings') ?? 'Bookings');
                    const labelRevenue = @json(__('admin.dashboard.revenue_usd') ?? 'Revenue ($)');

                    // 1. Revenue & Bookings Bar Chart
                    const revCtx = document.getElementById('revenueChart').getContext('2d');
                    new Chart(revCtx, {
                        type: 'bar',
                        data: {
                            labels: chartLabels,
                            datasets: [
                                {
                                    label: labelBookings,
                                    data: bookingsData,
                                    backgroundColor: '#1877C9',
                                    borderRadius: 4,
                                    yAxisID: 'y'
                                },
                                {
                                    label: labelRevenue,
                                    data: revenueData,
                                    backgroundColor: '#16a34a',
                                    borderRadius: 4,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false } },
                                y: { 
                                    type: 'linear', display: true, position: document.dir === 'rtl' ? 'right' : 'left', 
                                    grid: { borderDash: [4, 4] },
                                    ticks: { precision: 0 }
                                },
                                y1: { 
                                    type: 'linear', display: true, position: document.dir === 'rtl' ? 'left' : 'right', 
                                    grid: { display: false }
                                }
                            }
                        }
                    });

                    // 2. Top Tours Doughnut Chart
                    if (topTourLabels.length > 0) {
                        const topCtx = document.getElementById('topToursChart').getContext('2d');
                        new Chart(topCtx, {
                            type: 'doughnut',
                            data: {
                                labels: topTourLabels,
                                datasets: [{
                                    data: topTourData,
                                    backgroundColor: ['#1877C9', '#16a34a', '#F5B400', '#E63946', '#0A2540'],
                                    borderWidth: 2,
                                    borderColor: '#ffffff',
                                    cutout: '65%'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { 
                                    legend: { 
                                        position: 'bottom',
                                        labels: { padding: 20, usePointStyle: true, pointStyle: 'circle', font: { size: 11 } }
                                    } 
                                }
                            }
                        });
                    }
                }
            }));
        });
    </script>
</x-filament-panels::page>