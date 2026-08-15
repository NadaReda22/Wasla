
<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-screen-2xl mx-auto space-y-8" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        
        <!-- ==================== 1. PAGE HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-[#0A2540]">Operations Dashboard</h1>
                <p class="text-gray-500 text-sm mt-0.5">Wednesday, 9 July 2026 · Sharm El-Sheikh</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="bg-[#1877C9] text-white px-4 py-2 text-sm font-semibold rounded-lg hover:bg-[#1460a8] transition transform active:scale-95">
                    + New Booking
                </button>
            </div>
        </div>

        <!-- ==================== 2. KPI GRID ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <!-- Bookings -->
            <div class="bg-white rounded-2xl border-2 border-[#1877C9]/20 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-[#1877C9]/10 rounded-xl flex items-center justify-center text-[#1877C9]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="bg-[#16a34a]/10 text-[#16a34a] text-xs font-bold px-2 py-0.5 rounded-full">↑ Good</span>
                </div>
                <div class="text-2xl font-extrabold text-[#0A2540] mb-1 tabular-nums">47</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">Today's Bookings</div>
                <div class="text-xs text-gray-500">+12 since yesterday</div>
            </div>

            <!-- Occupancy -->
            <div class="bg-white rounded-2xl border-2 border-[#17A2B8]/20 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-[#17A2B8]/10 rounded-xl flex items-center justify-center text-[#17A2B8]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <span class="bg-[#16a34a]/10 text-[#16a34a] text-xs font-bold px-2 py-0.5 rounded-full">↑ Good</span>
                </div>
                <div class="text-2xl font-extrabold text-[#0A2540] mb-1 tabular-nums">78%</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">Seat Occupancy</div>
                <div class="text-xs text-gray-500">6 of 8 trips above 70%</div>
            </div>

            <!-- Pending Cash -->
            <div class="bg-white rounded-2xl border-2 border-amber-300 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">Action</span>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 mb-1 tabular-nums">9</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">Pending Cash</div>
                <div class="text-xs text-gray-500">Bookings awaiting payment</div>
            </div>

            <!-- Revenue -->
            <div class="bg-white rounded-2xl border-2 border-[#16a34a]/20 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-[#16a34a]/10 rounded-xl flex items-center justify-center text-[#16a34a]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <span class="bg-[#16a34a]/10 text-[#16a34a] text-xs font-bold px-2 py-0.5 rounded-full">↑ Good</span>
                </div>
                <div class="text-2xl font-extrabold text-[#16a34a] mb-1 tabular-nums">4,820 EGP</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">Today's Revenue</div>
                <div class="text-xs text-gray-500">Online: 3,200 · Cash: 1,620</div>
            </div>

            <!-- Active Trips -->
            <div class="bg-white rounded-2xl border-2 border-[#0A2540]/20 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-10 h-10 bg-[#0A2540]/10 rounded-xl flex items-center justify-center text-[#0A2540]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#0A2540] mb-1 tabular-nums">8</div>
                <div class="text-xs font-semibold text-[#0A2540] mb-0.5">Active Trips Today</div>
                <div class="text-xs text-gray-500">2 departing in next 60 min</div>
            </div>
        </div>

        <!-- ==================== 3. CHARTS ROW ==================== -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6" x-data="dashboardCharts()" x-init="initCharts()">
            <!-- Bar Chart (Revenue) -->
            <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="font-bold text-[#0A2540] text-base">Weekly Bookings & Revenue</h3>
                        <p class="text-gray-500 text-xs mt-0.5">This week · Sharm El-Sheikh operations</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 bg-[#1877C9] rounded-sm"></div>
                            <span class="text-gray-500">Bookings</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 bg-[#16a34a] rounded-sm"></div>
                            <span class="text-gray-500">Revenue</span>
                        </div>
                    </div>
                </div>
                <div class="relative h-[240px] w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Pie Chart (Payment Split) -->
            <div class="xl:col-span-1 bg-white rounded-2xl border border-gray-200 p-6 shadow-sm flex flex-col">
                <div class="mb-4">
                    <h3 class="font-bold text-[#0A2540] text-base">Payment Method Split</h3>
                    <p class="text-gray-500 text-xs mt-0.5">Today · 47 total bookings</p>
                </div>
                <div class="flex-1 flex flex-col items-center justify-center">
                    <div class="relative h-[160px] w-full flex justify-center">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <div class="w-full space-y-2 mt-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-[#1877C9]"></div>
                                <span class="text-xs text-gray-500">Online (Fawaterk)</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-[#0A2540] tabular-nums">28</span>
                                <span class="text-xs text-gray-500 ml-1">(60%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-[#16a34a]"></div>
                                <span class="text-xs text-gray-500">Cash on Board</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-[#0A2540] tabular-nums">19</span>
                                <span class="text-xs text-gray-500 ml-1">(40%)</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 w-full bg-amber-50 rounded-xl px-3 py-2.5 border border-amber-200">
                        <p class="text-amber-700 text-xs font-medium">
                            🇷🇺 8 Russian tourists auto-assigned to cash — banking restrictions applied
                        </p>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardCharts', () => ({
                initCharts() {
                    // Revenue Bar Chart
                    const revCtx = document.getElementById('revenueChart').getContext('2d');
                    new Chart(revCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                            datasets: [
                                {
                                    label: 'Bookings',
                                    data: [31, 28, 47, 52, 61, 74, 68],
                                    backgroundColor: (ctx) => ctx.dataIndex === 2 ? '#1877C9' : 'rgba(24,119,201,0.3)',
                                    borderRadius: 4,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Revenue',
                                    data: [3240, 2980, 4820, 5610, 6740, 8320, 7450],
                                    backgroundColor: (ctx) => ctx.dataIndex === 2 ? '#16a34a' : 'rgba(22,163,74,0.3)',
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
                                y: { type: 'linear', display: true, position: 'left', grid: { borderDash: [4, 4] } },
                                y1: { type: 'linear', display: true, position: 'right', grid: { display: false } }
                            }
                        }
                    });

                    // Payment Pie Chart
                    const payCtx = document.getElementById('paymentChart').getContext('2d');
                    new Chart(payCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Online (Fawaterk)', 'Cash on Board'],
                            datasets: [{
                                data: [28, 19],
                                backgroundColor: ['#1877C9', '#16a34a'],
                                borderWidth: 0,
                                cutout: '70%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            }));
        });
    </script>
</x-filament-panels::page>

