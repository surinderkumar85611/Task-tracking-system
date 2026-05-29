import './bootstrap'

import '../css/global.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'

import Toast from "vue-toastification"
import "vue-toastification/dist/index.css"

import { createPinia } from "pinia";

createInertiaApp({
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