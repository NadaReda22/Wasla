@php
    $brandName = $settings?->brand_name ?? 'Wasla';
    // Fetch the live logo, or fall back to the static asset if none exists
    $favicon = $settings?->brand_logo ? \Illuminate\Support\Facades\Storage::url($settings->brand_logo) : asset('images/logo.png');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName }}</title>
    
    <!-- Browser Tab Icon (Favicon) -->
    <link rel="icon" href="{{ $favicon }}" type="image/png">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
   
</head>
<body class="bg-white text-navy font-sans antialiased flex flex-col min-h-screen">

    <x-navbar />

    <main class="flex-1">
        @yield('content')
    </main>

    <x-footer />
    <x-whatsapp-button />

</body>
</html>