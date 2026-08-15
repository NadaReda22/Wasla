@php
    $settings = \App\Models\Setting::first();
    $brandName = $settings?->brand_name ?: 'Wasla';
    $slogan = $settings?->brand_slogan ?: 'Connected Journeys';
    $logo = $settings?->brand_logo ? \Illuminate\Support\Facades\Storage::url($settings->brand_logo) : asset('images/logo.png');
@endphp

<div style="display: flex; align-items: center; gap: 0.5rem;">
    <!-- Logo Container -->
    <div class="w-12 h-12 flex items-center justify-center shrink-0">

        <img src="{{ $logo }}" alt="{{ $brandName }} logo" class="w-full h-full object-contain scale-[2.7] hover:scale-[2.3] transition-transform origin-center">
    </div>
    
    <div style="display: flex; flex-direction: column;">
      
        <span style="font-size: 1.25rem; font-weight: 800; letter-spacing: -0.025em; color: #FFFFFF;">
            {{ $brandName }}
        </span>
        <span style="font-size: 0.75rem; font-weight: 500; color: #17A2B8;">
            {{ $slogan }}
        </span>
    </div>
</div>