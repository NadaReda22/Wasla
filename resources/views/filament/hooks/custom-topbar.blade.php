@php
    $settings = \App\Models\Setting::first();
    $adminName = $settings?->admin_name ?: 'Amr'; // Default set to Amr
    $currentLocale = app()->getLocale();
    
    // Auto-generate initials for the fallback avatar (using mb_ functions for Arabic/Russian support)
    $nameParts = explode(' ', trim($adminName));
    $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? mb_substr($nameParts[1], 0, 1) : ''));
    
    $adminImage = $settings?->admin_image ? \Illuminate\Support\Facades\Storage::url($settings->admin_image) : null;
@endphp

<div class="flex items-center gap-3 me-4" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    
    <!-- Language Switcher Dropdown -->
    <div x-data="{ open: false }" class="relative flex items-center">
        <button @click="open = !open" class="flex items-center gap-1.5 p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 font-bold text-xs uppercase tracking-wider">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
            {{ $currentLocale }}
        </button>
        
        <div x-show="open" @click.away="open = false" x-transition class="absolute top-full end-0 mt-1 w-32 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50" style="display: none;">
            <a href="{{ route('admin.lang.switch', 'en') }}" class="block px-4 py-2 text-sm {{ $currentLocale === 'en' ? 'bg-[#f0f7ff] text-[#1877c9] font-bold' : 'text-gray-700 hover:bg-gray-50' }}">English</a>
            <a href="{{ route('admin.lang.switch', 'ar') }}" class="block px-4 py-2 text-sm {{ $currentLocale === 'ar' ? 'bg-[#f0f7ff] text-[#1877c9] font-bold' : 'text-gray-700 hover:bg-gray-50' }}">العربية</a>
        </div>
    </div>

    <!-- Notification Bell -->
    <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        <span class="absolute top-1.5 end-1.5 w-2 h-2 bg-[#E63946] rounded-full animate-pulse"></span>
    </button>

    <!-- User Profile Block (Sand Color) -->
    <div class="flex items-center gap-2.5 bg-[#F0E4D0] rounded-xl px-3 py-2">
        
        @if($adminImage)
            <img src="{{ $adminImage }}" alt="{{ $adminName }}" class="w-7 h-7 rounded-full object-cover">
        @else
            <div class="w-7 h-7 bg-[#1877C9] rounded-full flex items-center justify-center text-white text-xs font-bold">
                {{ $initials }}
            </div>
        @endif

        <div class="hidden sm:block text-start">
            <div class="text-xs font-semibold text-[#0A2540] leading-tight">{{ $adminName }}</div>
            <div class="text-[10px] text-gray-500 leading-tight">{{ __('admin.topbar.operations_manager') ?? 'Operations Manager' }}</div>
        </div>
    </div>

  <!-- LOGOUT BUTTON -->
<form method="POST" action="{{ filament()->getLogoutUrl() }}" class="m-0 p-0 flex items-center">
    @csrf
    <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="{{ __('admin.topbar.logout') ?? 'Logout' }}">
        <svg class="w-5 h-5 shrink-0 {{ $currentLocale === 'ar' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span class="hidden sm:block">{{ __('admin.topbar.logout') ?? 'Logout' }}</span>
    </button>
</form>
</div>