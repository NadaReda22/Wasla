<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wasla</title>
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