import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                pm: {
                    // Pet&Co-inspired warm orange, white & black palette
                    orange:          '#EA580C',
                    'orange-dk':     '#C2410C',
                    'orange-lt':     '#FFF3EB',
                    'orange-subtle': '#FFF7ED',
                    // Primary accent alias for backward compatibility
                    green:           '#EA580C',
                    'green-dk':      '#C2410C',
                    'green-lt':      '#FFF3EB',
                    // Warm peach/apricot accent
                    peach:           '#FDBA74',
                    'peach-lt':      'rgba(253,186,116,0.22)',
                    // Charcoal/black
                    charcoal:        '#18181B',
                    'charcoal-lt':   '#27272A',
                    border:          '#EAE8E3',
                    muted:           '#71717A',
                    cream:           '#FCFBF9',
                    surface:         '#FFFFFF',
                },
            },
            boxShadow: {
                'xs':     '0 1px 2px rgba(37,48,43,0.06)',
                'card':   '0 2px 8px rgba(37,48,43,0.08)',
                'hover':  '0 8px 24px rgba(37,48,43,0.12)',
                'inner-sm': 'inset 0 1px 2px rgba(37,48,43,0.06)',
            },
            borderRadius: {
                '2xl': '1rem',
                '3xl': '1.5rem',
                '4xl': '2rem',
            },
            transitionDuration: {
                '250': '250ms',
            },
        },
    },

    plugins: [forms],
};
