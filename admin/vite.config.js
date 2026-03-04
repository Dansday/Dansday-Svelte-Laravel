import path from 'path';
import { fileURLToPath } from 'url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

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
        outDir: path.resolve(__dirname, 'public/assets/admin/css'),
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
