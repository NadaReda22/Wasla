<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Manifest</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 font-sans antialiased text-gray-900 pb-12">

    <div class="max-w-md mx-auto">
        <!-- Header -->
        <div class="bg-blue-600 text-white rounded-xl p-5 shadow-lg mb-6 text-center">
            <h1 class="text-xl font-bold uppercase tracking-wide">Route Manifest</h1>
            <p class="text-blue-100 text-sm mt-1">{{ $displayDate }}</p>
            <div class="mt-4 bg-white/20 rounded-lg p-3 text-left space-y-1">
                <p>👤 <strong>Driver:</strong> {{ $vehicle->driver_name ?? 'Unassigned' }}</p>
                <p>📱 <strong>Phone:</strong> {{ $vehicle->driver_phone ?? '-' }}</p>
                <p>🚙 <strong>Plate:</strong> {{ $vehicle->plate_number }}</p>
            </div>
        </div>

        @php
            $dailyTotalCash = 0;
            $dailyOutboundPax = 0;
            $dailyReturnPax = 0;
        @endphp

        @php
            $departureGroups = collect($groupedByTour)
                ->flatten(1)
                ->groupBy(fn ($r) => $r->tour_id . '-' . ($r->tour_departure_id ?? 'default'))
                ->sortBy(function ($group) {
                    $time = $group->first()->departure?->departure_time;

                    return $time ? \Carbon\Carbon::parse($time)->format('H:i') : '09:00';
                });
        @endphp

        <!-- Tours Loop (one card per tour + departure time) -->
        @foreach($departureGroups as $groupKey => $tourGroup)
            @php
                $sampleRecord = $tourGroup->first();
                $tour = $sampleRecord->tour;

                $outboundRecords = $tourGroup->filter(fn ($r) =>
                    $r->outbound_vehicle_id === $vehicle->id
                    && $r->payment_status === 'paid'
                    && $r->is_outbound_ensured
                );

                $returnRecords = $tourGroup->filter(fn ($r) =>
                    $r->return_vehicle_id === $vehicle->id
                    && $r->payment_status === 'paid'
                    && $r->is_return_ensured
                );

                $goTime = $sampleRecord->departure?->departure_time ? \Carbon\Carbon::parse($sampleRecord->departure->departure_time)->format('g:i A') : '09:00 AM';
                $retTime = $sampleRecord->departure?->return_time ? \Carbon\Carbon::parse($sampleRecord->departure->return_time)->format('g:i A') : 'TBD';

                $tourTotalCash = 0;
                $tourStopsCount = 0;
            @endphp

            @if($outboundRecords->isNotEmpty() || $returnRecords->isNotEmpty())
            <div class="bg-white rounded-xl shadow-md mb-6 overflow-hidden border border-gray-200">
                <div class="bg-gray-800 text-white p-4">
                    <h2 class="font-bold text-lg">🚩 {{ $tour->name_en ?? 'Unknown Tour' }}</h2>
                    <p class="text-gray-300 text-sm mt-1">🕐 Out: {{ $goTime }} | Ret: {{ $retTime }}</p>
                </div>

                <div class="p-4 space-y-6">
                    <!-- Outbound -->
                    @if($outboundRecords->isNotEmpty())
                        <div>
                            <h3 class="font-bold text-green-700 flex items-center mb-3">
                                🛫 OUTBOUND ({{ $goTime }})
                                <span class="ml-auto text-xs bg-green-100 px-2 py-1 rounded-full">{{ $outboundRecords->count() }} stops</span>
                            </h3>
                            <div class="space-y-4">
                                @php $stopNum = 1; @endphp
                                @foreach($outboundRecords as $record)
                                    @php
                                        $cash = ($record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? (float) $record->cash_due_on_board : 0;
                                        $dailyOutboundPax += $record->seats_count;
                                        $dailyTotalCash += $cash;
                                        $tourTotalCash += $cash;
                                        $tourStopsCount++;

                                        $areaName = filled($record->area?->name_en) ? $record->area->name_en : 'N/A';
                                        // عدّل اسم العمود هنا لو عنوان الفندق عندك باسم تاني
                                        $fullAddress = filled($record->pickup_address ?? null) ? $record->pickup_address : 'N/A';
                                        $mapUrl = \App\Services\Messaging\BookingWhatsAppMessage::mapUrl($record);
                                    @endphp
                                    <div class="pl-3 border-l-4 border-green-500">
                                        <p class="font-bold text-gray-800">#{{ $stopNum++ }} - {{ $record->client->full_name }}</p>
                                        <p class="text-sm text-gray-500 font-mono">Ref: {{ $record->booking_reference }}</p>

                                        <p class="text-sm text-gray-700 mt-1">🏨 <strong>Hotel:</strong> {{ filled($record->pickup_name) ? $record->pickup_name : 'N/A' }}</p>
                                        <p class="text-sm text-gray-700">🗺 <strong>Area:</strong> {{ $areaName }}</p>
                                        <p class="text-sm text-gray-700">📍 <strong>Address:</strong> {{ $fullAddress }}</p>
                                        <p class="text-sm text-gray-700">📌 <strong>Location:</strong>
                                            @if($mapUrl)
                                                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Open in Google Maps</a>
                                            @else
                                                N/A
                                            @endif
                                        </p>

                                        <p class="text-sm text-gray-700 mt-1">🤝 <strong>Meet:</strong> {{ !empty($record->reception_number) ? $record->reception_number : 'Main Lobby/Reception' }}</p>
                                        <p class="text-sm text-gray-700">🚪 <strong>Room:</strong> {{ filled($record->room_number) ? $record->room_number : 'N/A' }}</p>

                                        <p class="text-sm text-gray-600 mt-1">💺 {{ $record->seats_count }} Pax | 📱 <a href="tel:{{ $record->client->whatsapp_number }}" class="text-blue-600 underline">{{ $record->client->whatsapp_number }}</a></p>

                                        @if($cash > 0)
                                            <p class="text-sm font-bold text-red-600 mt-1 bg-red-50 p-1 rounded inline-block">💰 Collect: ${{ number_format($cash, 2) }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Return -->
                    @if($returnRecords->isNotEmpty())
                        <div>
                            <h3 class="font-bold text-orange-700 flex items-center mb-3">
                                🛬 RETURN ({{ $retTime }})
                                <span class="ml-auto text-xs bg-orange-100 px-2 py-1 rounded-full">{{ $returnRecords->count() }} stops</span>
                            </h3>
                            <div class="space-y-4">
                                @php $stopNum = 1; @endphp
                                @foreach($returnRecords as $record)
                                    @php
                                        $collectHere = $record->outbound_vehicle_id !== $vehicle->id;
                                        $cash = ($collectHere && $record->cash_due_on_board > 0 && !$record->is_cash_on_board_paid) ? (float) $record->cash_due_on_board : 0;
                                        $dailyReturnPax += $record->seats_count;
                                        $dailyTotalCash += $cash;
                                        $tourTotalCash += $cash;
                                        $tourStopsCount++;

                                        $areaName = filled($record->area?->name_en) ? $record->area->name_en : 'N/A';
                                        // عدّل اسم العمود هنا لو عنوان الفندق عندك باسم تاني
                                        $fullAddress = filled($record->pickup_address ?? null) ? $record->pickup_address : 'N/A';
                                        $mapUrl = \App\Services\Messaging\BookingWhatsAppMessage::mapUrl($record);
                                    @endphp
                                    <div class="pl-3 border-l-4 border-orange-500">
                                        <p class="font-bold text-gray-800">#{{ $stopNum++ }} - {{ $record->client->full_name }}</p>
                                        <p class="text-sm text-gray-500 font-mono">Ref: {{ $record->booking_reference }}</p>

                                        <p class="text-sm text-gray-700 mt-1">🏨 <strong>Hotel:</strong> {{ filled($record->pickup_name) ? $record->pickup_name : 'N/A' }}</p>
                                        <p class="text-sm text-gray-700">🗺 <strong>Area:</strong> {{ $areaName }}</p>
                                        <p class="text-sm text-gray-700">📍 <strong>Address:</strong> {{ $fullAddress }}</p>
                                        <p class="text-sm text-gray-700">📌 <strong>Location:</strong>
                                            @if($mapUrl)
                                                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline">Open in Google Maps</a>
                                            @else
                                                N/A
                                            @endif
                                        </p>

                                        <p class="text-sm text-gray-700 mt-1">🤝 <strong>Meet:</strong> {{ !empty($record->reception_number) ? $record->reception_number : 'Main Lobby/Reception' }}</p>
                                        <p class="text-sm text-gray-700">🚪 <strong>Room:</strong> {{ filled($record->room_number) ? $record->room_number : 'N/A' }}</p>

                                        <p class="text-sm text-gray-600 mt-1">💺 {{ $record->seats_count }} Pax | 📱 <a href="tel:{{ $record->client->whatsapp_number }}" class="text-blue-600 underline">{{ $record->client->whatsapp_number }}</a></p>

                                        @if($cash > 0)
                                            <p class="text-sm font-bold text-red-600 mt-1 bg-red-50 p-1 rounded inline-block">💰 Collect: ${{ number_format($cash, 2) }}</p>
                                        @endif
                                    </div>
                                @endforeach

                                @if($tour->is_bus_staying)
                                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm font-semibold flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Vehicle stays on-site during this tour.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tour Totals -->
                <div class="bg-gray-50 p-4 border-t border-gray-200 flex justify-between items-center text-sm">
                    <span class="font-semibold text-gray-600">Total Stops: {{ $tourStopsCount }}</span>
                    <span class="font-bold text-gray-900">To Collect: <span class="text-green-600">${{ number_format($tourTotalCash, 2) }}</span></span>
                </div>
            </div>
            @endif
        @endforeach

        <!-- Daily Summary Footer -->
        <div class="bg-gray-800 text-white rounded-xl p-5 shadow-lg">
            <h3 class="font-bold uppercase border-b border-gray-600 pb-2 mb-2">Daily Totals</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-400">Going Pax</p>
                    <p class="text-xl font-bold">{{ $dailyOutboundPax }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Return Pax</p>
                    <p class="text-xl font-bold">{{ $dailyReturnPax }}</p>
                </div>
                <div class="col-span-2 mt-2">
                    <p class="text-gray-400">Total Cash to Collect Today</p>
                    <p class="text-2xl font-bold text-green-400">${{ number_format($dailyTotalCash, 2) }}</p>
                </div>
            </div>
        </div>

    </div>
</body>
</html>