import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: { admin: 'resources/sass/admin.scss' },
            refresh: true,
            buildDirectory: 'assets/admin/css',
        }),
        tailwindcss(),
    ],
    build: {
        outDir: 'public/assets/admin/css',
        rollupOptions: {
            output: {
                assetFileNames: () => 'admin.css',
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
