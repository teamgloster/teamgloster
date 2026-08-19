import "./bootstrap";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import AppToaster from "@/Components/AppToaster.vue";
import { useToast } from "@/composables/useNotify";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.vue", { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () =>
                h("div", { class: "app-root" }, [
                    h(App, props),
                    h(AppToaster),
                ]),
        });

        app.use(plugin);
        app.use(ZiggyVue);
        app.config.globalProperties.$notify = useToast();
        app.mount(el);
    },
});
