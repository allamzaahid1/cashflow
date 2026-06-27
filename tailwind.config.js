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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'bg-base': 'var(--bg-base)',
                'bg-surface': 'var(--bg-surface)',
                'border-base': 'var(--border-base)',
                'text-primary': 'var(--text-primary)',
                'text-secondary': 'var(--text-secondary)',
                'success-bg': 'var(--success-bg)',
                'success-text': 'var(--success-text)',
                'danger-bg': 'var(--danger-bg)',
                'danger-text': 'var(--danger-text)',
                'warning-bg': 'var(--warning-bg)',
                'warning-text': 'var(--warning-text)',
            },
        },
    },

    plugins: [forms],
};
