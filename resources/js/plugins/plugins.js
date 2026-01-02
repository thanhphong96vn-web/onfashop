import helpers from "../utils/helpers";
import { createApp } from "vue";
const app = createApp({});

export default {
    install: (app) => {
        app.helpers = helpers;
        app.config.globalProperties.$helpers = helpers;
        app.config.globalProperties.imageFallback = function (
            e,
            size = "square"
        ) {
            // Chỉ set placeholder 1 lần, tránh infinite loop
            if (!e.target.dataset.fallbackLoaded) {
                e.target.dataset.fallbackLoaded = "true";
                e.target.src = helpers.imagePlaceholder(size);
                // Remove error handler để không trigger lại
                e.target.onerror = null;
            }
        };
    },
};

// export default {
//     install: (Vue) => {
//         Vue.helpers = helpers
//         Vue.prototype.$helpers = helpers

//         Vue.prototype.imageFallback = function(e,size = 'square') {
//             e.target.src = helpers.imagePlaceholder(size);
//         };
//     }
// }
