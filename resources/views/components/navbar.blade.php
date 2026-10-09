@php
    $brandName = $settings?->brand_name ?? 'Wasla';
    $slogan = $settings?->brand_slogan ?? 'Connected Journeys';
    $logo = $settings?->brand_logo ? \Illuminate\Support\Facades\Storage::url($settings->brand_logo) : asset('images/logo.png');

    $navLinks = [
        ['to' => url('/'), 'label' => __('nav.home')],
        ['to' => url('/#tours'), 'label' => __('nav.todays_tours')],
        ['to' => url('/#about'), 'label' => __('nav.about_us')],
        ['to' => url('/instructions'), 'label' => __('nav.instructions')],
        ['to' => url('/faq'), 'label' => __('nav.faq')],
        ['to' => url('/#coverage'), 'label' => __('nav.coverage_area')],
    ];

      if (!($settings?->hide_coverage_area)) {
        $navLinks[] = ['to' => url('/#coverage'), 'label' => __('nav.coverage_area')];
    }

    $legalLinks = [
        ['to' => url('/#policies'), 'label' => __('nav.policies_overview')],
        ['to' => route('terms'), 'label' => __('nav.terms')],
        ['to' => route('refund-policy'), 'label' => __('nav.refund_policy')],
        ['to' => route('privacy'), 'label' => __('nav.privacy_policy')],
    ];

    $locales = ['en' => 'EN', 'ru' => 'RU'];
@endphp

<header id="main-navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-transparent py-4">
    <nav class="container-max px-4 sm:px-6 lg:px-8 flex items-center justify-between">

        <!-- BRAND LOGO & TEXT -->
        <a href="{{ url('/') }}" class="flex items-center group shrink-0">
            <div class="w-20 h-20 flex items-center justify-center shrink-0 overflow-visible">
                <img src="{{ $logo }}" alt="{{ $brandName }} logo" class="max-w-none w-50 h-32 group-hover:scale-[1.3] object-contain">
            </div>
            <div class="flex flex-col justify-center items-start text-left ml-4 z-8">
                <span id="nav-brand-title" class="font-display font-bold text-3xl leading-none text-gold tracking-widest">
                    {{ $brandName }}
                </span>
                <span id="nav-brand-subtitle" class="text-xs font-semibold text-gold/80 tracking-wide mt-1 ml-[-4px] whitespace-nowrap">
                    {{ $slogan }}
                </span>
            </div>
        </a>

        <!-- DESKTOP LINKS -->
        <div class="hidden xl:flex items-center gap-0.5 flex-nowrap">
            @foreach ($navLinks as $link)
                <a href="{{ $link['to'] }}" class="desktop-nav-link whitespace-nowrap px-3 py-2 rounded-lg font-semibold transition-all duration-200 text-sm text-white hover:bg-white/10">
                    {{ $link['label'] }}
                </a>
            @endforeach

            {{-- LEGAL DROPDOWN: Policies overview + Terms + Refund + Privacy --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open" type="button"
                        class="desktop-nav-link whitespace-nowrap px-3 py-2 rounded-lg font-semibold transition-all duration-200 text-sm text-white hover:bg-white/10 flex items-center gap-1">
                    {{ __('nav.policies') }}
                    <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition x-cloak
                     class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    @foreach ($legalLinks as $link)
                        <a href="{{ $link['to'] }}" @click="open = false"
                           class="block px-4 py-2.5 text-sm font-semibold text-navy hover:bg-primary/10 hover:text-primary transition-colors">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- LANGUAGE SWITCHER --}}
            <div class="lang-switcher flex items-center gap-1 border border-white/20 rounded-full p-1 ml-2 shrink-0">
                @foreach ($locales as $code => $label)
                    <a href="{{ route('lang.switch', $code) }}"
                       class="lang-switch-link whitespace-nowrap px-3 py-1 text-xs font-bold rounded-full transition-colors {{ app()->getLocale() === $code ? 'bg-gold text-navy' : 'text-white/70 hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Hide on /book route -->
            @if(!request()->is('book'))
                <a href="{{ url('/book') }}" class="btn-gold ml-2 text-sm shrink-0 whitespace-nowrap">
                    {{ __('nav.book_now') }}
                    <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            @endif
        </div>

        <!-- MOBILE MENU BUTTON -->
        <button id="mobile-menu-button" class="xl:hidden p-2 rounded-lg text-white" aria-label="Menu">
            <svg id="menu-icon-open" class="w-6 h-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg id="menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </nav>

    <!-- MOBILE MENU DROPDOWN -->
    <div id="mobile-menu" class="hidden xl:hidden bg-white shadow-xl border-t border-gray-100">
        <div class="px-4 py-4 space-y-1">
            @foreach ($navLinks as $link)
                <a href="{{ $link['to'] }}" class="block px-4 py-3 rounded-lg font-semibold text-navy hover:bg-primary/10 hover:text-primary transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach

            {{-- LEGAL SECTION (mobile) --}}
            <details class="group">
                <summary class="flex items-center justify-between px-4 py-3 rounded-lg font-semibold text-navy hover:bg-primary/10 hover:text-primary transition-colors cursor-pointer list-none marker:hidden">
                    {{ __('nav.policies') }}
                    <svg class="w-4 h-4 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="pl-4 space-y-1 pb-1">
                    @foreach ($legalLinks as $link)
                        <a href="{{ $link['to'] }}" class="block px-4 py-2.5 rounded-lg text-sm font-semibold text-navy/70 hover:bg-primary/10 hover:text-primary transition-colors">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </div>
            </details>

            {{-- LANGUAGE SWITCHER (mobile) --}}
            <div class="flex items-center gap-2 px-4 py-3">
                @foreach ($locales as $code => $label)
                    <a href="{{ route('lang.switch', $code) }}"
                       class="px-3 py-1.5 text-xs font-bold rounded-full border transition-colors {{ app()->getLocale() === $code ? 'bg-navy text-white border-navy' : 'text-navy/70 border-navy/10' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Hide on /book route -->
            @if(!request()->is('book'))
                <a href="{{ url('/book') }}" class="btn-gold w-full mt-2 inline-block text-center">
                    {{ __('nav.book_now') }}
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
        const langLinks = document.querySelectorAll('.lang-switch-link');
        const mobileBtn = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        const iconOpen = document.getElementById('menu-icon-open');
        const iconClose = document.getElementById('menu-icon-close');

        let isScrolled = false;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 20 && !isScrolled) {
                isScrolled = true;
                header.classList.remove('bg-transparent', 'py-4');
                header.classList.add('bg-white', 'shadow-lg', 'py-2');
                title.classList.remove('text-gold');
                title.classList.add('text-primary');
                subtitle.classList.remove('text-gold/80');
                subtitle.classList.add('text-primary');
                mobileBtn.classList.remove('text-white');
                mobileBtn.classList.add('text-primary');

                desktopLinks.forEach(link => {
                    link.classList.remove('text-white', 'hover:bg-white/10');
                    link.classList.add('text-primary', 'hover:bg-primary/10');
                });

                langLinks.forEach(link => {
                    if (!link.classList.contains('bg-gold')) {
                        link.classList.remove('text-white/70', 'hover:text-white');
                        link.classList.add('text-primary/70', 'hover:text-primary');
                    }
                });

            } else if (window.scrollY <= 20 && isScrolled) {
                isScrolled = false;
                header.classList.remove('bg-white', 'shadow-lg', 'py-2');
                header.classList.add('bg-transparent', 'py-4');
                title.classList.remove('text-primary');
                title.classList.add('text-gold');
                subtitle.classList.remove('text-primary');
                subtitle.classList.add('text-gold/80');
                mobileBtn.classList.remove('text-primary');
                mobileBtn.classList.add('text-white');

                desktopLinks.forEach(link => {
                    link.classList.remove('text-primary', 'hover:bg-primary/10');
                    link.classList.add('text-white', 'hover:bg-white/10');
                });

                langLinks.forEach(link => {
                    if (!link.classList.contains('bg-gold')) {
                        link.classList.remove('text-primary/70', 'hover:text-primary');
                        link.classList.add('text-white/70', 'hover:text-white');
                    }
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