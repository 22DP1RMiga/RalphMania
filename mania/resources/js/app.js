import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import updateBackground from '../js/background.js';


const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Change background image on page load
document.addEventListener("DOMContentLoaded", () => {
    updateBackground(window.location.pathname);
});

// Change background image on Inertia navigation
if (window.Inertia) {
    document.addEventListener("inertia:navigate", (event) => {
        updateBackground(event.detail.page.url);
    });
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).then(() => {
    console.log('Inertia.js app is set up.');
});
