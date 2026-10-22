import defaultTheme from 'tailwindcss/defaultTheme';
import colors from 'tailwindcss/colors';
import forms from '@tailwindcss/forms';

const colorPalette = (name, palette, shades) => Object.fromEntries(
    shades.map((shade) => {
        const hex = palette[shade].slice(1);
        const rgb = [0, 2, 4].map((index) => Number.parseInt(hex.slice(index, index + 2), 16)).join(' ');

        return [shade, `rgb(var(--ui-${name}-${shade}, ${rgb}) / <alpha-value>)`];
    }),
);

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontSize: {
                xs: ['10px', { lineHeight: '14px' }],
                sm: ['12px', { lineHeight: '18px' }],
                base: ['12px', { lineHeight: '18px' }],
                lg: ['14px', { lineHeight: '20px' }],
                xl: ['14px', { lineHeight: '20px' }],
                '2xl': ['16px', { lineHeight: '24px' }],
                '3xl': ['20px', { lineHeight: '28px' }],
                '4xl': ['24px', { lineHeight: '32px' }],
                '5xl': ['32px', { lineHeight: '40px' }],
                '6xl': ['40px', { lineHeight: '48px' }],
                '7xl': ['48px', { lineHeight: '56px' }],
                '8xl': ['64px', { lineHeight: '72px' }],
                '9xl': ['80px', { lineHeight: '88px' }],
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                editorial: ['Playfair Display', 'Georgia', 'serif'],
            },
            colors: {
                gray: colorPalette('gray', colors.gray, Object.keys(colors.gray).filter((shade) => /^\d+$/.test(shade))),
                slate: colorPalette('slate', colors.slate, Object.keys(colors.slate).filter((shade) => /^\d+$/.test(shade))),
                red: colorPalette('red', colors.red, Object.keys(colors.red).filter((shade) => /^\d+$/.test(shade))),
                green: colorPalette('green', colors.green, Object.keys(colors.green).filter((shade) => /^\d+$/.test(shade))),
                amber: colorPalette('amber', colors.amber, Object.keys(colors.amber).filter((shade) => /^\d+$/.test(shade))),
                emerald: colorPalette('emerald', colors.emerald, Object.keys(colors.emerald).filter((shade) => /^\d+$/.test(shade))),
                indigo: colorPalette('indigo', colors.indigo, Object.keys(colors.indigo).filter((shade) => /^\d+$/.test(shade))),
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
