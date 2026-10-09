@php
    $settings = \App\Models\Setting::first();
    $dbPolicies = is_array($settings?->legal_policies) ? $settings->legal_policies : [];
    $locale = app()->getLocale();

    // 1. Calculate Dynamic Pricing
    $depositAmount = $settings->deposit_amount_dollar ?? 1;
    $minTourCost = $settings->minimum_tour_cost_dollar ?? 4;
    $balanceAmount = max(0, $minTourCost - $depositAmount);

    $depositText = '$' . (float) $depositAmount; 
    $balanceText = '$' . (float) $balanceAmount;

    // 2. Reformat database policies into an easy-to-use array by key
    $policiesData = [];
    foreach ($dbPolicies as $p) {
        // THE FIX: Skip this row if the key is corrupted or missing from the database
        if (!isset($p['key'])) continue; 

        $policiesData[$p['key']] = [
            'title' => $locale === 'ru' ? ($p['title_ru'] ?? '') : ($p['title_en'] ?? ''),
            'items' => $locale === 'ru' ? ($p['items_ru'] ?? []) : ($p['items_en'] ?? []),
        ];
    }

    // 3. Keep the UI styles (Icons and Colors) safe from admin deletion
    $uiStyles = [
        ['key' => 'booking', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'text-primary', 'bg' => 'bg-primary/8'],
        ['key' => 'cancel', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => 'text-turquoise', 'bg' => 'bg-turquoise/8'],
        ['key' => 'conduct', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'text-gold', 'bg' => 'bg-gold/8'],
        ['key' => 'payment', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'text-coral', 'bg' => 'bg-coral/8'],
        ['key' => 'delay', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-navy', 'bg' => 'bg-navy/8'],
        ['key' => 'privacy', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'text-primary-700', 'bg' => 'bg-primary-700/8'],
    ];
@endphp

<section class="section-padding bg-white" id="policies">
    <div class="container-max">
        <div class="text-center mb-14">
            <div class="inline-flex items-center gap-2 bg-navy/10 text-navy px-4 py-2 rounded-full text-sm font-semibold mb-4">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                {{ __('policies.badge') ?? 'Legal & Operations' }}
            </div>
            <h2 class="text-3xl sm:text-4xl font-display font-bold text-navy mb-4">{{ __('policies.heading') ?? 'Our Policies & Terms' }}</h2>
            <p class="text-navy/60 text-lg max-w-xl mx-auto">{{ __('policies.intro') ?? 'Transparent rules for a smooth journey.' }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($uiStyles as $ui)
                @php
                    $policy = $policiesData[$ui['key']] ?? null;
                    if (!$policy) continue; // Skip if database is missing this policy key
                @endphp
                
                <div class="card p-7">
                    <div class="w-12 h-12 {{ $ui['bg'] }} rounded-2xl flex items-center justify-center mb-5">
                        <svg class="w-5 h-5 {{ $ui['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ui['icon'] }}" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-navy text-lg mb-4">{{ $policy['title'] }}</h3>
                    <ul class="space-y-2.5">
                        @foreach ($policy['items'] as $item)
                            @php
                                // Clean, bulletproof dynamic injection mapping tags to live pricing
                                $ruleText = str_replace(
                                    [':deposit', ':balance'], 
                                    [$depositText, $balanceText], 
                                    $item['item'] ?? ''
                                );
                            @endphp
                            <li class="flex items-start gap-2.5 text-sm text-navy/60 leading-relaxed">
                                <span class="w-1.5 h-1.5 rounded-full mt-2 flex-shrink-0 {{ str_replace('text-', 'bg-', $ui['color']) }}"></span>
                                {{ $ruleText }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</section>