<footer class="gradient-navy text-white pt-16 pb-8 mt-auto">
    <div class="container-max px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <!-- BRAND LOGO & TEXT -->
        <a href="{{ url('/') }}" class="flex items-center group">
            
            <!-- Logo Container -->
            <div class="w-16 h-16 flex items-center justify-center shrink-0">
                <!-- Scale 2.2 blows up the image to hide the transparent borders. Origin-center keeps it balanced. -->
                <img src="{{ asset('images/logo.png') }}" alt="Wasla logo" class="w-full h-full object-contain scale-[2.3] group-hover:scale-[2.3] transition-transform origin-center">
            </div>
                    <div>
                        <h3 class="font-display font-bold text-xl">Wasla</h3>
                        <p class="text-gold text-sm">Tourist Transport</p>
                    </div>
                </div>
                <p class="text-white/70 leading-relaxed max-w-md">
                    The first tourist transport platform in Sharm El Sheikh. We provide comfortable,
                    air-conditioned buses at affordable prices. Book your seat in minutes and enjoy a
                    safe, comfortable journey.
                </p>
            </div>

            <div>
                <h4 class="font-bold text-gold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                    <li><a href="{{ url('/') }}" class="text-white/70 hover:text-gold transition-colors">Home</a></li>
                    <li><a href="{{ url('/booking') }}" class="text-white/70 hover:text-gold transition-colors">Book a Trip</a></li>
                    <li><a href="{{ url('/about') }}" class="text-white/70 hover:text-gold transition-colors">About Us</a></li>
                    <li><a href="{{ url('/instructions') }}" class="text-white/70 hover:text-gold transition-colors">Instructions</a></li>
                    <li><a href="{{ url('/faq') }}" class="text-white/70 hover:text-gold transition-colors">FAQ</a></li>
                    <li><a href="{{ url('/policies') }}" class="text-white/70 hover:text-gold transition-colors">Policies</a></li>
                    <li><a href="{{ url('/dashboard') }}" class="text-white/70 hover:text-gold transition-colors">Dashboard</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-gold mb-4">Contact Us</h4>
                <ul class="space-y-3">
                    <li class="flex items-center gap-2 text-white/70">
                        <svg class="w-5 h-5 text-turquoise flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                        </svg>
                        info@sharmtransit.com
                    </li>
                    <li class="flex items-center gap-2 text-white/70">
                        <svg class="w-5 h-5 text-turquoise flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z" />
                        </svg>
                        +20 100 000 0000
                    </li>
                    <li class="flex items-center gap-2 text-white/70">
                        <svg class="w-5 h-5 text-turquoise flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                        </svg>
                        Sharm El Sheikh, Egypt
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-white/50 text-sm">
                © 2026 Wasla. All rights reserved.
            </p>
            <div class="flex items-center gap-4">
                <span class="badge-gold text-xs">Daily Tourist Transport</span>
            </div>
        </div>
    </div>
</footer>