@extends('layouts.app')
@section('content')

@php
    $settings = \App\Models\Setting::first();
    $dbPolicies = is_array($settings?->legal_policies) ? $settings->legal_policies : [];
    $locale = app()->getLocale();

    $policy = collect($dbPolicies)->firstWhere('key', 'cancel');
    $title = $locale === 'ru' ? ($policy['title_ru'] ?? 'Refund Policy') : ($policy['title_en'] ?? 'Refund Policy');
    $items = $locale === 'ru' ? ($policy['items_ru'] ?? []) : ($policy['items_en'] ?? []);

    $dbPhone = preg_replace('/[^0-9]/', '', $settings->contact_phone ?? '');
    $whatsappNumber = !empty($dbPhone) ? $dbPhone : '201038344119';
    $brandName = $settings->brand_name ?? 'Sharm Transit';
    $whatsappMessage = __('policies.whatsapp_message', ['brand' => $brandName]);
    $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($whatsappMessage);
@endphp

<div class="min-h-screen pb-16 relative">
    <section class="gradient-navy text-white pt-32 pb-16">
        <div class="container-max px-4 text-center">
            <span class="badge-gold mb-4 inline-block">{{ __('policies.refund_badge') }}</span>
            <h1 class="text-3xl sm:text-5xl font-display font-bold mb-4">
                {{ $title }}
            </h1>
            <p class="text-white/70 max-w-2xl mx-auto text-lg">
                {{ __('policies.refund_intro') }}
            </p>
            <p class="text-white/40 text-sm mt-4">{{ __('policies.last_updated') }} {{ $settings->updated_at ? $settings->updated_at->format('F Y') : now()->format('F Y') }}</p>
        </div>
    </section>

    <section class="section-padding bg-sand-light">
        <div class="container-max max-w-3xl">
            <div class="card p-8">
                <ul class="space-y-4">
                    @foreach ($items as $item)
                        <li class="flex items-start gap-3 text-navy/70 leading-relaxed">
                            <span class="w-2 h-2 rounded-full bg-turquoise mt-2 flex-shrink-0"></span>
                            <span>{{ $item['item'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="text-center mt-12">
                <p class="text-navy/60 mb-4">{{ __('policies.refund_questions') }}</p>
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