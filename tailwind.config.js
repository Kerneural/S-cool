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
                sans: ['"Be Vietnam Pro"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#0F766E',
                    hover: '#115E59',
                    soft: '#F0FDFA',
                    50: '#F0FDFA',
                    100: '#CCFBF1',
                    200: '#99F6E4',
                    300: '#5EEAD4',
                    400: '#2DD4BF',
                    500: '#0F766E',
                    600: '#0F766E',
                    700: '#115E59',
                    800: '#134E4A',
                    900: '#134E4A',
                    950: '#042F2E',
                },
                canvas: '#F8FAFC',
                surface: '#FFFFFF',
                ink: '#0F172A',
                muted: '#475569',
                line: '#E2E8F0',
                'control-line': '#64748B',
                reward: '#B45309',
                danger: '#B91C1C',
            },
        },
    },

    plugins: [forms],
};
