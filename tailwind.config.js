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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                editorial: ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                aequitas: {
                    50: 'var(--aequitas-navy-50)',
                    100: 'var(--aequitas-navy-100)',
                    700: 'var(--aequitas-navy-700)',
                    800: 'var(--aequitas-navy-800)',
                    900: 'var(--aequitas-navy-900)',
                    950: 'var(--aequitas-navy-950)',
                },
                gold: {
                    50: 'var(--aequitas-gold-50)',
                    100: 'var(--aequitas-gold-100)',
                    300: 'var(--aequitas-gold-300)',
                    600: 'var(--aequitas-gold-600)',
                    700: 'var(--aequitas-gold-700)',
                },
                ink: 'var(--aequitas-ink)',
                muted: 'var(--aequitas-muted)',
                canvas: 'var(--aequitas-canvas)',
                line: 'var(--aequitas-line)',
                positive: 'var(--aequitas-positive)',
                negative: 'var(--aequitas-negative)',
                'negative-hover': 'var(--aequitas-negative-hover)',
                'negative-active': 'var(--aequitas-negative-active)',
            },
            borderRadius: {
                control: '0.625rem',
                card: '0.875rem',
                panel: '1rem',
            },
            boxShadow: {
                panel: '0 1px 3px 0 rgb(16 26 42 / 0.08), 0 1px 2px -1px rgb(16 26 42 / 0.08)',
                floating: '0 12px 28px -12px rgb(16 26 42 / 0.22)',
            },
        },
    },

    plugins: [forms],
};
