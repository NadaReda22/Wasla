@php
    $settings = \App\Models\Setting::first();
    $adminName = $settings?->admin_name ?: 'Amr'; // Default set to Amr
    
    // Auto-generate initials for the fallback avatar
    $nameParts = explode(' ', trim($adminName));
    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
    
    $adminImage = $settings?->admin_image ? \Illuminate\Support\Facades\Storage::url($settings->admin_image) : null;
@endphp

<div class="flex items-center gap-3 mr-4" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <!-- Notification Bell -->
    <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors">
        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-[#E63946] rounded-full animate-pulse"></span>
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

        <div class="hidden sm:block text-left">
            <div class="text-xs font-semibold text-[#0A2540] leading-tight">{{ $adminName }}</div>
            <div class="text-[10px] text-gray-500 leading-tight">Operations Manager</div>
        </div>
    </div>

  <!-- LOGOUT BUTTON -->
<form method="POST" action="{{ filament()->getLogoutUrl() }}" class="m-0 p-0 flex items-center">
    @csrf
    <button type="submit" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Logout">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        <span>Logout</span>
    </button>
</form>
</div>