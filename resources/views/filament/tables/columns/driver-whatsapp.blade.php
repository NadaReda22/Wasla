@php
    use App\Services\Messaging\BookingWhatsAppMessage;

    $record = $getRecord();
    $out = $record->outboundVehicle;
    $ret = $record->returnVehicle;

    $showOut = filled($out?->driver_phone);
    $showRet = $record->trip_type === 'round_trip'
        && filled($ret?->driver_phone)
        && $record->return_vehicle_id !== $record->outbound_vehicle_id;

    $btn = 'display:block;width:fit-content;padding:3px 7px;background-color:#25D366;color:#ffffff;'
         . 'font-size:11px;font-weight:bold;text-align:center;border-radius:4px;text-decoration:none;'
         . 'white-space:nowrap;box-shadow:0 1px 2px rgba(0,0,0,0.1);';
@endphp

<div style="display:flex;flex-direction:column;gap:4px;">
    @if ($showOut)
        <a href="{{ BookingWhatsAppMessage::forDriver($record, $out, 'outbound') }}"
           target="_blank" rel="noopener noreferrer"
           onclick="event.stopPropagation()"
           style="{{ $btn }}">🛫 {{ $out->driver_phone }}</a>
    @endif

    @if ($showRet)
        <a href="{{ BookingWhatsAppMessage::forDriver($record, $ret, 'return') }}"
           target="_blank" rel="noopener noreferrer"
           onclick="event.stopPropagation()"
           style="{{ $btn }}">🛬 {{ $ret->driver_phone }}</a>
    @endif

    @if (! $showOut && ! $showRet)
        <span>-</span>
    @endif
</div>