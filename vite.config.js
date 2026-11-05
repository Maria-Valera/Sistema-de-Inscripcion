import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/HomePage/script.js',
                'resources/css/home/BoostrapHome.css',
                'resources/css/home/Home.css',
                'resources/css/home/Encabezado.css',
                'resources/css/home/vidaEstudiantil.css',
            ],
            refresh: true,
        }),
    ],
    
});
