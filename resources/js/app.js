import './bootstrap'

import '../css/global.css'

import { createApp, h } from 'vue'
import { CkeditorPlugin } from '@ckeditor/ckeditor5-vue'
import 'ckeditor5/ckeditor5.css'
import { createInertiaApp, router } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

import Toast from "vue-toastification"
import "vue-toastification/dist/index.css"

import { createPinia } from "pinia";

router.on('navigate', (event) => {
    const guestPaths = ['/login', '/register', '/forgot-password', '/'];
    if (guestPaths.includes(window.location.pathname)) {
        if (window.performance && window.performance.navigation.type === 2) {
            window.location.reload();
        }
    }
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});

createInertiaApp({
    title: (title) => title ? `${title} | Time Tracker` : 'Time Tracker',
    resolve: name =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {

        const pinia = createPinia();

        createApp({ render: () => h(App, props) })
    .use(plugin)
    .use(pinia)
    .use(Toast, {
        position: "top-right",
        timeout: 3000,
    })
    .mount(el)
    },
})