import axios from "axios";
import store from "../store/store";
export default {
    mounted() {
        window.addEventListener("DOMContentLoaded", this.device);
    },
    methods: {
        call_api: (method, url, data, multipart = false) => {
            let config = {
                method: method,
                url: store.state.app.apiPath + url,
                data: data,
                headers: {
                    'Content-Type': multipart ? "multipart/form-data" : 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('shopAccessToken') || null}`, 
                  },
            };

            // if (multipart) {
            //     config.headers = { "content-type": "multipart/form-data" };
            // }

            try {
                let res = axios(config);
                return res;
            } catch (e) {
                // return e.response
            }
        },
        static_asset: (path = "") => {
            // If path is already a full URL (starts with http:// or https://), return as is
            if (path.startsWith('http://') || path.startsWith('https://')) {
                return path;
            }
            
            // If path is empty, return empty
            if (!path || path === '') {
                return '';
            }
            
            // Remove leading slash if path starts with /
            const cleanPath = path.startsWith('/') ? path : '/' + path;
            
            // Check current URL structure to determine base path
            const currentPath = window.location.pathname;
            const pathParts = currentPath.split('/').filter(p => p && p !== '');
            
            // If URL contains 'onfashop' or similar subdirectory, include it
            let basePath = '';
            if (pathParts.length > 0 && pathParts[0] !== 'public') {
                // Check if we're in a subdirectory (like /onfashop)
                const firstPart = pathParts[0];
                if (firstPart && !['index.php', 'public'].includes(firstPart)) {
                    basePath = '/' + firstPart;
                }
            }
            
            // Always include /public for static assets in XAMPP/Laravel setup
            return `${basePath}/public${cleanPath}`;
        },
        snack: function(data = {}) {
            // Translate message if it's a translation key
            if (data.message && typeof data.message === 'string') {
                data.message = this.translateMessage(data.message);
            }
            store.commit("snackbar/createSnack", data);
        },
        translateMessage: function(message) {
            if (!message || typeof message !== 'string') {
                return message;
            }
            
            // Try to translate the message using $t (Vue I18n)
            // $t is available in Vue components that use this mixin
            try {
                // Check if $t is available (preferred method)
                if (this.$t && typeof this.$t === 'function') {
                    const translated = this.$t(message);
                    // If translation returns the same key, it means translation not found
                    if (translated !== message && translated) {
                        return translated;
                    }
                }
                
                // Fallback to $i18n.t if $t is not available
                if (this.$i18n && this.$i18n.t && typeof this.$i18n.t === 'function') {
                    const translated = this.$i18n.t(message);
                    // If translation returns the same key, it means translation not found
                    if (translated !== message && translated) {
                        return translated;
                    }
                }
                
                // If no translation found, return original message (might be plain text)
                return message;
            } catch (e) {
                // If translation fails, return original message
                console.warn('Translation error:', e);
                return message;
            }
        },
        format_price: (amount = 0) => {
            amount = parseFloat(amount);
            amount = typeof amount == "number" ? amount : 0.0;

            let currency_setting =
                store.getters["app/generalSettings"].currency;
            let formated_price = amount.toFixed(
                currency_setting.no_of_decimals
            );

            if (currency_setting.truncate_price == 1) {
                if (formated_price > 999999.99 && formated_price < 1000000000) {
                    formated_price =
                        (formated_price / 1000000).toFixed(
                            currency_setting.no_of_decimals
                        ) + " triệu";
                } else if (formated_price > 1000000000) {
                    formated_price =
                        (formated_price / 1000000000).toFixed(
                            currency_setting.no_of_decimals
                        ) + " tỷ";
                }
            }

            formated_price =
                currency_setting.decimal_separator == "1"
                    ? formated_price.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
                    : formated_price
                          .replace(".", ",")
                          .replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");

            switch (currency_setting.symbol_format) {
                case "2":
                    formated_price = formated_price + currency_setting.code;
                    break;
                case "3":
                    formated_price =
                        currency_setting.code + " " + formated_price;
                    break;
                case "4":
                    formated_price =
                        formated_price + " " + currency_setting.code;
                    break;
                default:
                    formated_price = currency_setting.code + formated_price;
            }
            return formated_price;
        },
        is_addon_activated: (key = "") => {
            let addon = store.getters["app/addons"].find(
                (addon) => addon.unique_identifier === key
            );
            return (
                key &&
                key != "" &&
                typeof addon != "undefined" &&
                addon.activated == 1
            );
        },
        is_empty_obj: (obj = {}) => {
            return (
                obj &&
                Object.keys(obj).length === 0 &&
                obj.constructor === Object
            );
        },
        discount_percent: (price = 0, discount_price = 0) => {
            price = parseFloat(price);
            discount_price = parseFloat(discount_price);

            return price > discount_price
                ? Math.round((100 * (price - discount_price)) / price)
                : 0;
        },
        get_random_color: () => {
            var colors = Array(
                "red",
                "pink",
                "purple",
                "deep-purple",
                "indigo",
                "blue",
                "blue",
                "light-blue",
                "cyan",
                "teal",
                "green",
                "light-green",
                "lime",
                "yellow",
                "amber",
                "orange",
                "deep-orange",
                "brown",
                "blue-grey",
                "grey"
            );
            return colors[Math.floor(Math.random() * colors.length)];
        },
        device() {
            let viewportWidth = window.innerWidth;
            if (viewportWidth >= 1904) {
                return "xl";
            } else if (viewportWidth < 1904 && viewportWidth >= 1264) {
                return "lg";
            } else if (viewportWidth < 1264 && viewportWidth >= 960) {
                return "md";
            } else if (viewportWidth < 960 && viewportWidth >= 600) {
                return "sm";
            } else {
                return "xs";
            }
        },
        $optional: (p) => eval("this." + p),
        setSession: (key, status) => {
            let now = new Date();
            let item = {
                status: status,
                expiry: now.getTime() + 3600000,
            };

            localStorage.setItem(key, JSON.stringify(item));
        },
        checkSession: (key) => {
            let item = {};
            if (localStorage.getItem(key)) {
                item = localStorage.getItem(key);
                item = JSON.parse(item);
            }
            let now = new Date();

            return typeof item.expiry == "undefined" ||
                now.getTime() > item.expiry
                ? ""
                : item.status;
        },
    },
};
