@php
    $brandName = $settings?->brand_name ?? 'Wasla';
    $slogan = $settings?->brand_slogan ?? 'Connected Journeys';
    $logo = $settings?->brand_logo ? \Illuminate\Support\Facades\Storage::url($settings->brand_logo) : asset('images/logo.png');

    $navLinks = [
        ['to' => url('/'), 'label' => 'Home'],
        ['to' => url('/#tours'), 'label' => "Today's Tours"],
        ['to' => url('/#about'), 'label' => 'About Us'],
        ['to' => url('/#policies'), 'label' => 'Policies'],
        ['to' => url('/instructions'), 'label' => 'Instructions'],
        ['to' => url('/faq'), 'label' => 'FAQ'],
        ['to' => url('/#coverage'), 'label' => 'Coverage Area'],     
    ];
@endphp

<header id="main-navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent py-4">
    <nav class="container-max px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        
        <!-- BRAND LOGO & TEXT -->
        <a href="{{ url('/') }}" class="flex items-center group">
            
            <!-- Logo Container -->
            <div class="w-20 h-20 flex items-center justify-center shrink-0 overflow-visible">
                <img src="{{ $logo }}" alt="{{ $brandName }} logo" class="max-w-none w-50 h-32 group-hover:scale-[1.3] object-contain">
            </div>

            <!-- Text Container -->
            <div class="flex flex-col justify-center items-start text-left ml-4 z-8">
                <span id="nav-brand-title" class="font-display font-bold text-3xl leading-none text-gold tracking-widest">
                    {{ $brandName }}
                </span>
                <span id="nav-brand-subtitle" class="text-xs font-semibold text-gold/80 tracking-wide mt-1 ml-[-4px]">
                    {{ $slogan }}
                </span>
            </div>
        </a>

        <!-- DESKTOP LINKS -->
        <div class="hidden lg:flex items-center gap-1">
            @foreach ($navLinks as $link)
                <a href="{{ $link['to'] }}" class="desktop-nav-link px-4 py-2 rounded-lg font-semibold transition-all duration-200 text-sm text-white hover:bg-white/10">
                    {{ $link['label'] }}
                </a>
            @endforeach
            
            <!-- Hide on /book route -->
            @if(!request()->is('book'))
                <a href="{{ url('/book') }}" class="btn-gold ml-2 text-sm">
                    Book Now
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            @endif
        </div>

        <!-- MOBILE MENU BUTTON -->
        <button id="mobile-menu-button" class="lg:hidden p-2 rounded-lg text-white" aria-label="Menu">
            <svg id="menu-icon-open" class="w-6 h-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </nav>

    <!-- MOBILE MENU DROPDOWN -->
    <div id="mobile-menu" class="hidden lg:hidden bg-white shadow-xl border-t border-gray-100">
        <div class="px-4 py-4 space-y-1">
            @foreach ($navLinks as $link)
                <a href="{{ $link['to'] }}" class="block px-4 py-3 rounded-lg font-semibold text-navy hover:bg-primary/10 hover:text-primary transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach
            
            <!-- Hide on /book route -->
            @if(!request()->is('book'))
                <a href="{{ url('/book') }}" class="btn-gold w-full mt-2 inline-block text-center">
                    Book Now
                </a>
            @endif
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('main-navbar');
        const title = document.getElementById('nav-brand-title');
        const subtitle = document.getElementById('nav-brand-subtitle');
        const desktopLinks = document.querySelectorAll('.desktop-nav-link');
        const mobileBtn = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');

        let isScrolled = false;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 20 && !isScrolled) {
                isScrolled = true;
                
                // 1. Header Background
                header.classList.remove('bg-transparent', 'py-4');
                header.classList.add('bg-white', 'shadow-lg', 'py-2');
                
                // 2. Brand Text (Wasla -> Primary, Subtitle -> Primary)
                title.classList.remove('text-gold');
                title.classList.add('text-primary');
                
                subtitle.classList.remove('text-gold/80');
                subtitle.classList.add('text-primary');
                
                // 3. Mobile Button
                mobileBtn.classList.remove('text-white');
                mobileBtn.classList.add('text-primary');
                
                // 4. Desktop Nav Links (Change to Primary Blue)
                desktopLinks.forEach(link => {
                    link.classList.remove('text-white', 'hover:bg-white/10');
                    link.classList.add('text-primary', 'hover:bg-primary/10');
                });
                
            } else if (window.scrollY <= 20 && isScrolled) {
                isScrolled = false;
                
                // 1. Revert Background
                header.classList.remove('bg-white', 'shadow-lg', 'py-2');
                header.classList.add('bg-transparent', 'py-4');
                
                // 2. Revert Brand Text (Wasla -> Gold, Subtitle -> Gold/80)
                title.classList.remove('text-primary');
                title.classList.add('text-gold');
                
                subtitle.classList.remove('text-primary');
                subtitle.classList.add('text-gold/80');
                
                // 3. Revert Mobile Button
                mobileBtn.classList.remove('text-primary');
                mobileBtn.classList.add('text-white');
                
                // 4. Revert Desktop Nav Links (Back to White)
                desktopLinks.forEach(link => {
                    link.classList.remove('text-primary', 'hover:bg-primary/10');
                    link.classList.add('text-white', 'hover:bg-white/10');
                });
            }
        });

        mobileBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            iconOpen.classList.toggle('hidden');
            iconOpen.classList.toggle('block');
            iconClose.classList.toggle('hidden');
            iconClose.classList.toggle('block');
        });
    });
</script>