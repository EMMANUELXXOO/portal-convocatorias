import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
      darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
             colors: {
                'cr-red': '#C1121F',
                'cr-blue': '#1D3557',
                'cr-sky': '#457B9D',
                'cr-ice': '#E8F1F8',
            },
            boxShadow: {
                soft: '0 15px 35px -15px rgba(15, 23, 42, 0.35)',
            },
        },
    },

    plugins: [forms],
};
