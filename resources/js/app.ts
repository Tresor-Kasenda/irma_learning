import '../css/app.css';

import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {createPinia} from 'pinia';
import {createApp, DefineComponent, h} from 'vue';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import confetti from 'canvas-confetti';
import {initializeUiTheme} from '@/stores/ui';

function applyApplicationTheme(settings: unknown): void {
    const primaryColor = (settings as {primary_color?: string} | undefined)?.primary_color;

    if (primaryColor && /^#[0-9a-f]{6}$/i.test(primaryColor)) {
        document.documentElement.style.setProperty('--irma-primary', primaryColor);
    }
}

window.confetti = confetti;
initializeUiTheme();

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({el, App, props, plugin}) {
        applyApplicationTheme(props.initialPage.props.appSettings);

        createApp({render: () => h(App, props)})
            .use(plugin)
            .use(createPinia())
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

document.addEventListener('inertia:navigate', (event) => {
    applyApplicationTheme((event as CustomEvent<{page?: {props?: {appSettings?: unknown}}}>).detail.page?.props?.appSettings);
});
