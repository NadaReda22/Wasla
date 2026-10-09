@extends('layouts.app')
@section('content')

@php
    $settings = \App\Models\Setting::first();
    $dbPolicies = is_array($settings?->legal_policies) ? $settings->legal_policies : [];
    $locale = app()->getLocale();

    $policiesData = [];
    foreach ($dbPolicies as $p) {
        if (!isset($p['key'])) continue; 
        $policiesData[$p['key']] = [
            'title' => $locale === 'ru' ? ($p['title_ru'] ?? '') : ($p['title_en'] ?? ''),
            'items' => $locale === 'ru' ? ($p['items_ru'] ?? []) : ($p['items_en'] ?? []),
        ];
    }

    $termsKeys = [
        ['key' => 'booking', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'text-primary', 'bg' => 'bg-primary/8'],
        ['key' => 'conduct', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'text-gold', 'bg' => 'bg-gold/8'],
        ['key' => 'payment', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h16a3 3 0 003-3V8a3 3 0 00-3-3H5a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'text-coral', 'bg' => 'bg-coral/8'],
        ['key' => 'delay', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-navy', 'bg' => 'bg-navy/8'],
    ];

    // Dynamic WhatsApp Setup with fallback to your specific number
    $dbPhone = preg_replace('/[^0-9]/', '', $settings->contact_phone ?? '');
    $whatsappNumber = !empty($dbPhone) ? $dbPhone : '201038344119';
    $brandName = $settings->brand_name ?? 'Sharm Transit';
    $whatsappMessage = __('policies.whatsapp_message', ['brand' => $brandName]);
    $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($whatsappMessage);
@endphp

<div class="min-h-screen pb-16 relative">
    <section class="gradient-navy text-white pt-32 pb-16">
        <div class="container-max px-4 text-center">
            <span class="badge-gold mb-4 inline-block">{{ __('policies.badge') }}</span>
            <h1 class="text-3xl sm:text-5xl font-display font-bold mb-4">
                {{ __('policies.terms_heading') }}
            </h1>
            <p class="text-white/70 max-w-2xl mx-auto text-lg">
                {{ __('policies.terms_intro') }}
            </p>
            <p class="text-white/40 text-sm mt-4">{{ __('policies.last_updated') }} {{ $settings->updated_at ? $settings->updated_at->format('F Y') : now()->format('F Y') }}</p>
        </div>
    </section>

    <section class="section-padding bg-sand-light">
        <div class="container-max">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($termsKeys as $ui)
                    @php
                        $policy = $policiesData[$ui['key']] ?? null;
                        if (!$policy) continue; 
                    @endphp
                    
                    <div class="card p-7">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-12 h-12 {{ $ui['bg'] }} rounded-2xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 {{ $ui['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ui['icon'] }}" />
                                </svg>
                            </div>
                            <h2 class="font-bold text-navy text-lg">{{ $policy['title'] }}</h2>
                        </div>
                        <ul class="space-y-2.5">
                            @foreach ($policy['items'] as $item)
                                <li class="flex items-start gap-2.5 text-sm text-navy/60 leading-relaxed">
                                    <span class="w-1.5 h-1.5 rounded-full mt-2 flex-shrink-0 {{ str_replace('text-', 'bg-', $ui['color']) }}"></span>
                                    {{ $item['item'] ?? '' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <p class="text-navy/60 mb-4">{{ __('policies.terms_questions') }}</p>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn-turquoise inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                    </svg>
                    {{ __('policies.whatsapp_btn') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Shared WhatsApp Component -->
    @include('components.whatsapp-button')
</div>
@endsection