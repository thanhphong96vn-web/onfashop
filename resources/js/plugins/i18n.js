import { createI18n } from "vue-i18n";
import axios from "axios";

let shopSelectedLanguage = localStorage.getItem("shopSelectedLanguage") || "en";

// Initialize with empty messages to prevent errors before loading
const i18n = createI18n({
    locale: shopSelectedLanguage,
    fallbackLocale: "en",
    silentTranslationWarn: true,
    legacy: true, // Use Options API mode for compatibility with this.$i18n.t and this.$t
    globalInjection: true, // Inject $t, $i18n, etc. into component instances
    messages: {
        [shopSelectedLanguage]: {},
        en: {}
    }
});

const loadedLanguages = [];

function setI18nLanguage(lang, data) {
    if (!loadedLanguages.includes(lang)) {
        loadedLanguages.push(lang);
    }
    
    // For legacy mode (Options API), locale is a writable property
    i18n.global.locale = lang;
    
    if (data) {
        i18n.global.setLocaleMessage(lang, data);
    }
}

async function loadLanguageAsync(lang) {
    if (!lang) {
        lang = shopSelectedLanguage || "en";
    }
    
    // Check if already loaded
    if (loadedLanguages.includes(lang)) {
        const currentLocale = i18n.global.locale;
        if (currentLocale !== lang) {
            setI18nLanguage(lang);
        }
        return Promise.resolve();
    }
    
    try {
        const response = await axios.get(`/api/v1/locale/${lang}`);
        if (response && response.status === 200 && response.data) {
            // API returns JSON string, parse if needed
            let messages = response.data;
            if (typeof messages === 'string') {
                try {
                    messages = JSON.parse(messages);
                } catch (e) {
                    console.error('Failed to parse locale messages:', e);
                    messages = {};
                }
            }
            setI18nLanguage(lang, messages);
            return Promise.resolve();
        } else {
            // Fallback to English if no data
            console.warn(`No locale data for ${lang}, falling back to English`);
            if (!loadedLanguages.includes("en")) {
                // Try to load English
                return loadLanguageAsync("en");
            }
            return Promise.resolve();
        }
    } catch (error) {
        console.error('Failed to load language:', lang, error);
        // Fallback to English on error
        if (!loadedLanguages.includes("en") && lang !== "en") {
            // Try to load English
            return loadLanguageAsync("en");
        }
        return Promise.resolve();
    }
}

export { i18n, loadLanguageAsync, setI18nLanguage };
