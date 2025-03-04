import '../css/app.css';
import './bootstrap';

import {createInertiaApp, router} from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
// import { createRouter, createWebHistory } from 'vue-router'
import { createApp, h } from 'vue';
import updateBackground from './background.js';
document.addEventListener("DOMContentLoaded", updateBackground(window.location.pathname));

if (window.Inertia) {
    document.addEventListener("inertia:navigate", (event) => {
        updateBackground(event.detail.page.url);
    });
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({el, App, props, plugin}) {
        return createApp({render: () => h(App, props)})
            .use(plugin)
            // .use(ZiggyVue)
            //.use(router)
            // .use(store)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
}).then(() => {
    console.log('Inertia.js app is set up with Vue Router.');
});
