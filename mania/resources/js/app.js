import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import '@fortawesome/fontawesome-free/css/all.min.css'
import updateBackground from './background.js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    // setup({ el, App, props, plugin }) {
    //     return createApp({ render: () => h(App, props) })
    //         .use(plugin)
    //         .use(ZiggyVue)
    //         .mount(el);
    // },
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Listen for Inertia navigation
        vueApp.mixin({
            mounted() {
                updateBackground(window.location.pathname);
            },
            watch: {
                '$page.url': function(newUrl) {
                    const path = new URL(newUrl, window.location.origin).pathname;
                    updateBackground(path);
                }
            }
        });

        return vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
