@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#fdfbf7] flex items-center justify-center px-4">
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 max-w-md w-full text-center">
        <div class="w-12 h-12 mx-auto mb-5 rounded-full border-4 border-[#1877c9]/20 border-t-[#1877c9] animate-spin"></div>
        <h1 class="text-2xl font-extrabold text-navy mb-2">Confirming your payment…</h1>
        <p class="text-sm text-navy/60 mb-4">
            This usually takes a few seconds. This page refreshes automatically.
        </p>
        <p class="text-xs font-bold text-navy/50">Reference: {{ $booking->booking_reference }}</p>
    </div>
</div>

<script>
    // Reload every 4 seconds, for up to about 2 minutes
    (function () {
        var tries = 0;
        try { tries = parseInt(sessionStorage.getItem('pay_poll_{{ $booking->id }}') || '0', 10); } catch (e) {}
        if (tries >= 30) return;
        try { sessionStorage.setItem('pay_poll_{{ $booking->id }}', String(tries + 1)); } catch (e) {}
        setTimeout(function () { window.location.reload(); }, 4000);
    })();
</script>
@endsection