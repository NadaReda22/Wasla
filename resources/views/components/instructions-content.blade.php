@php
    $settings = $settings ?? \App\Models\Setting::first();
    $defaults = \Database\Seeders\BookingInstructionsSeeder::defaults();

    $isRu = app()->getLocale() === 'ru';

    // Pick the Russian text when the site is in Russian and a translation exists, otherwise English
    $tr = fn ($row, $key) => ($isRu && !empty($row[$key . '_ru']))
        ? $row[$key . '_ru']
        : ($row[$key] ?? '');

    // 1. Steps (DB first, seeder defaults as fallback)
    $steps = collect($settings?->booking_steps ?: $defaults['booking_steps'])
        ->values()
        ->map(fn ($step, $i) => array_merge($step, [
            'number' => !empty($step['number']) ? $step['number'] : str_pad($i + 1, 2, '0', STR_PAD_LEFT),
            'icon'   => !empty($step['icon']) ? $step['icon'] : '•',
        ]));

    // 2. Dos
    $doRows = !empty($settings?->booking_dos) ? $settings->booking_dos : $defaults['booking_dos'];
    $doList = collect($doRows)->map(fn ($row) => $tr($row, 'item'))->filter()->values()->all();

    // 3. Don'ts
    $dontRows = !empty($settings?->booking_donts) ? $settings->booking_donts : $defaults['booking_donts'];
    $dontList = collect($dontRows)->map(fn ($row) => $tr($row, 'item'))->filter()->values()->all();

    // 4. Important notes
    $importantNotes = $settings?->important_notes ?: $defaults['important_notes'];
@endphp

{{-- Steps --}}
<div class="mb-16">
    <h2 class="text-2xl sm:text-3xl font-display font-bold text-navy mb-10 text-center">{{ __('instructions.steps') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach ($steps as $step)
            <div class="bg-sand rounded-3xl p-7 border border-sand-dark relative overflow-hidden">
                <div class="absolute top-4 right-4 text-4xl font-bold text-navy/10 font-tabular select-none">{{ $step['number'] }}</div>
                <div class="text-3xl mb-4">{{ $step['icon'] }}</div>
                <h3 class="font-bold text-navy text-base mb-2">{{ $tr($step, 'title') }}</h3>
                <p class="text-navy/60 text-sm leading-relaxed">{{ $tr($step, 'desc') }}</p>
            </div>
        @endforeach
    </div>
</div>

{{-- Do / Don't --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
    <div class="bg-green-50 rounded-3xl p-8 border border-green-200">
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <h3 class="font-bold text-navy text-xl">{{ __('instructions.do') }}</h3>
        </div>
        <ul class="space-y-3">
            @foreach ($doList as $item)
                <li class="flex items-start gap-3 text-sm text-navy leading-relaxed">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </div>
    <div class="bg-red-50 rounded-3xl p-8 border border-red-200">
        <div class="flex items-center gap-3 mb-6">
            <svg class="w-6 h-6 text-coral" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            <h3 class="font-bold text-navy text-xl">{{ __('instructions.dont') }}</h3>
        </div>
        <ul class="space-y-3">
            @foreach ($dontList as $item)
                <li class="flex items-start gap-3 text-sm text-navy leading-relaxed">
                    <svg class="w-4 h-4 text-coral flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </div>
</div>

{{-- Important notes --}}
<div class="bg-amber-50 border-2 border-amber-300 rounded-3xl p-8">
    <h3 class="font-bold text-amber-800 text-xl mb-6 flex items-center gap-2">
        <svg class="w-5.5 h-5.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        {{ __('instructions.notes') }}
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach ($importantNotes as $note)
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-amber-200 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $note['icon'] ?? '' }}" /></svg>
                </div>
                <div>
                    <div class="font-bold text-amber-800 text-sm mb-1">{{ $tr($note, 'title') }}</div>
                    <p class="text-amber-700 text-xs leading-relaxed">{{ $tr($note, 'text') }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>