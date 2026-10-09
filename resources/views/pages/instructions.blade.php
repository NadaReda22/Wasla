@extends('layouts.app')
@section('content')
<div class="min-h-screen pb-16 relative">
    @php
        $settings = \App\Models\Setting::first();
    @endphp

    {{-- Hero --}}
    <div class="hero-gradient py-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 bg-gold/20 text-gold px-4 py-2 rounded-full text-sm font-semibold mb-6">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                {{ __('instructions.badge') }}
            </div>
            <h1 class="text-4xl sm:text-5xl font-display font-bold text-white mb-6">{{ __('instructions.title') }}</h1>
            <p class="text-white/80 text-lg leading-relaxed mb-8">
                {{ __('instructions.intro') }}
            </p>
            <a href="{{ route('booking.create') }}" class="btn-coral px-8 py-4 text-base rounded-2xl inline-flex">
                {{ __('instructions.read_book') }}
            </a>
        </div>
    </div>

    <div class="container-max px-4 sm:px-6 lg:px-8 py-20">
        {{-- Steps, Do/Don't, Important notes --}}
        <div class="mb-16">
            @include('components.instructions-content', ['settings' => $settings])
        </div>

        {{-- CTA --}}
        <div class="text-center">
            <h3 class="text-2xl font-display font-bold text-navy mb-4">{{ __('instructions.ready') }}</h3>
            <p class="text-navy/60 mb-6">{{ __('instructions.cta_text') }}</p>
            <a href="{{ route('booking.create') }}" class="btn-coral px-10 py-4 text-base rounded-2xl inline-flex">
                {{ __('instructions.cta_button') }}
            </a>
        </div>
    </div>
    
    {{-- Shared WhatsApp Component --}}
    @include('components.whatsapp-button')
</div>
@endsection