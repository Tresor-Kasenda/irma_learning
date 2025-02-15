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
            },
            colors: {
                primary: {
                    DEFAULT: "hsl(var(--color-primary-600))",
                    50: "hsl(var(--color-primary-50))",
                    100: "hsl(var(--color-primary-100))",
                    200: "hsl(var(--color-primary-200))",
                    300: "hsl(var(--color-primary-300))",
                    400: "hsl(var(--color-primary-400))",
                    500: "hsl(var(--color-primary-500))",
                    600: "hsl(var(--color-primary-600))",
                    700: "hsl(var(--color-primary-700))",
                    800: "hsl(var(--color-primary-800))",
                    900: "hsl(var(--color-primary-900))",
                    950: "hsl(var(--color-primary-950))",
                },
                gray: {
                    DEFAULT: "hsl(var(--color-gray-600))",
                    50: "hsl(var(--color-gray-50))",
                    100: "hsl(var(--color-gray-100))",
                    200: "hsl(var(--color-gray-200))",
                    300: "hsl(var(--color-gray-300))",
                    400: "hsl(var(--color-gray-400))",
                    500: "hsl(var(--color-gray-500))",
                    600: "hsl(var(--color-gray-600))",
                    700: "hsl(var(--color-gray-700))",
                    800: "hsl(var(--color-gray-800))",
                    900: "hsl(var(--color-gray-900))",
                    950: "hsl(var(--color-gray-950))",
                },
                bg: {
                    DEFAULT: "hsl(var(--color-bg))",
                    dark: "hsl(var(--color-bg-dark))",
                    lighter: "hsl(var(--color-bg-lighter))",
                    light: "hsl(var(--color-bg-light))",
                    high: "hsl(var(--color-bg-high))",
                    higher: "hsl(var(--color-bg-higher))",
                },
                border: {
                    DEFAULT: "hsl(var(--color-border))",
                    dark: "hsl(var(--color-border-dark))",
                    light: "hsl(var(--color-border-light))",
                    lighter: "hsl(var(--color-border-lighter))",
                    high: "hsl(var(--color-border-high))",
                },
                fg: {
                    DEFAULT: "hsl(var(--color-fg))",
                    subtext: "hsl(var(--color-fg-subtext))",
                    subtitle: "hsl(var(--color-fg-subtitle))",
                    title: "hsl(var(--color-fg-title))",
                },
            },
        },
    },

    plugins: [forms, require("@flexilla/tailwind-plugin"),],
};
