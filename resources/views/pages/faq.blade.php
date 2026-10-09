@extends('layouts.app')
@section('content')

@php
    $settings = \App\Models\Setting::first();
    
    // Smart WhatsApp Setup
    $wa_dbPhone = preg_replace('/[^0-9]/', '', $settings->contact_phone ?? '');
    if (str_starts_with($wa_dbPhone, '00')) {
        $wa_dbPhone = substr($wa_dbPhone, 2);
    }
    if (str_starts_with($wa_dbPhone, '01') && strlen($wa_dbPhone) === 11) {
        $wa_dbPhone = '2' . $wa_dbPhone;
    }

    $wa_number = !empty($wa_dbPhone) ? $wa_dbPhone : '201038344119';
    $wa_brand = $settings->brand_name ?? 'Sharm Transit';
    $wa_msg = __('policies.whatsapp_message', ['brand' => $wa_brand]);
    $whatsappUrl = 'https://wa.me/' . $wa_number . '?text=' . rawurlencode($wa_msg);
@endphp

<div class="min-h-screen pb-16 relative"> 
    
    <section class="gradient-navy text-white pt-32 pb-16">
        <div class="container-max px-4 text-center">
            <span class="badge-gold mb-4 inline-block">{{ __('faq.badge') }}</span>
            <h1 class="text-3xl sm:text-5xl font-display font-bold mb-4">
                {{ __('faq.heading') }}
            </h1>
            <p class="text-white/70 max-w-2xl mx-auto text-lg">
                {{ __('faq.subheading') }}
            </p>
        </div>
    </section>

    <section class="section-padding bg-white">
        <div class="container-max">
            <div class="max-w-3xl mx-auto space-y-4">
                @forelse($faqs as $faq)
                    <details class="card p-5 group cursor-pointer">
                        <summary class="font-semibold text-navy flex items-center justify-between list-none marker:hidden">
                            {{ $faq->{'question_' . app()->getLocale()} ?? $faq->question_en }}
                            <svg class="w-5 h-5 text-primary transition-transform group-open:rotate-180 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="text-navy/60 mt-3 leading-relaxed prose prose-sm max-w-none">
                            {!! $faq->{'answer_' . app()->getLocale()} ?? $faq->answer_en !!}
                        </div>
                    </details>
                @empty
                    <div class="text-center text-navy/50 py-8">
                        {{ __('faq.empty') }}
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-12">
                <p class="text-navy/60 mb-4">{{ __('faq.still_have_questions') }}</p>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn-turquoise inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/>
                    </svg>
                    {{ __('faq.contact_whatsapp') }}
                </a>
            </div>
        </div>
    </section>

    <!-- Shared WhatsApp Component -->
    @include('components.whatsapp-button')
</div>
@endsection