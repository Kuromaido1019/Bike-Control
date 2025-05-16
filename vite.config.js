import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/sb-admin-2.css',
                'resources/js/sb-admin-2.js',
                'resources/js/app-vue.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
});
