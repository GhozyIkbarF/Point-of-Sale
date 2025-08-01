import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import Swal from 'sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(ElementPlus)
            .use(plugin)
            .use(ZiggyVue)
        
        app.config.globalProperties.$swal = Swal;
        window.Swal = Swal;
        
        return app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
