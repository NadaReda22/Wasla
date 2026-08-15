import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                navy: {
                    DEFAULT: '#0A2540',
                    light: '#14365E',
                    dark: '#061A30',
                },
                gold: {
                    DEFAULT: '#F5B400',
                    light: '#FFD24D',
                    dark: '#C99000',
                },
                coral: {
                    DEFAULT: '#E63946',
                },
                turquoise: {
                    DEFAULT: '#17A2B8',
                },
                sand: {
                    DEFAULT: '#F0E4D0',
                    light: '#F8F1E3',
                    dark: '#D9C9A8',
                }
            }
        }
    }
}