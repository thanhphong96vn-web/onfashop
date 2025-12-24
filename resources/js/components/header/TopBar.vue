<template>
    <div class="topbar">
        <div
            v-if="topBannerVisible && !loading && data.top_banner.img"
            class="position-relative"
        >
            <dynamic-link
                :to="data.top_banner.link"
                append-class="text-reset d-block lh-0"
            >
                <img :src="data.top_banner.img" class="img-fit h-50px w-100" />
            </dynamic-link>
            <v-btn
                elevation="0"
                fab
                outlined
                size="x-small"
                class="absolute-top-right rounded border-2 btn-xxs mt-2 me-2 transparent"
                color="white"
                v-on:click="closeTopBanner"
            >
                <i class="las la-times ts-10 text-white" />
            </v-btn>
        </div>

        <!-- Bottom band: màu vàng với 3 text căn giữa -->
        <div class="topbar-bottom-band">
            <v-container class="px-0" style="padding: 2px 0 !important" fluid>
                <div
                    class="d-flex align-center justify-between py-2 position-relative"
                >
                    <!-- Language Dropdown - ở đầu (bên trái) -->
                    <div
                        v-if="
                            allLanguages.length > 1 &&
                            data.show_language_switcher === 'on'
                        "
                        class="topbar-language-container"
                    >
                        <v-menu offset-y>
                            <template v-slot:activator="{ props }">
                                <div
                                    v-bind="props"
                                    class="topbar-language-switcher d-flex align-center c-pointer"
                                >
                                    <img
                                        :src="`/assets/img/flags/${userLanguageObj.flag}.png`"
                                        height="14"
                                        class="me-1"
                                        :alt="userLanguageObj.name"
                                    />
                                    <span class="topbar-text">{{
                                        userLanguageObj.name
                                    }}</span>
                                    <i class="las la-angle-down fs-12 ms-1"></i>
                                </div>
                            </template>
                            <v-list class="pa-0">
                                <v-list-item
                                    v-for="(language, i) in allLanguages"
                                    :key="i"
                                    @click="switchLanguage(language.code)"
                                    :class="{
                                        'bg-grey-lighten-4':
                                            userLanguageObj.code ===
                                            language.code,
                                    }"
                                >
                                    <v-list-item-title
                                        class="d-flex align-center"
                                    >
                                        <img
                                            :src="`/assets/img/flags/${language.flag}.png`"
                                            height="14"
                                            class="me-2"
                                            :alt="language.name"
                                        />
                                        <span class="fs-13">{{
                                            language.name
                                        }}</span>
                                    </v-list-item-title>
                                </v-list-item>
                            </v-list>
                        </v-menu>
                    </div>
                    <div v-else class="topbar-language-container"></div>

                    <!-- Các text căn giữa -->
                    <div class="d-flex align-center justify-center flex-grow-1">
                        <template v-if="is_addon_activated('multi_vendor')">
                            <router-link
                                :to="{ name: 'ShopRegistration' }"
                                class="topbar-link"
                            >
                                Bán hàng cùng Onfas
                            </router-link>
                        </template>
                        <template v-else>
                            <span class="topbar-text">
                                Bán hàng cùng Onfas
                            </span>
                        </template>
                        <v-divider vertical class="mx-3 topbar-divider" />
                        <a
                            v-if="
                                data.mobile_app_links?.play_store ||
                                data.mobile_app_links?.app_store
                            "
                            :href="
                                data.mobile_app_links?.play_store ||
                                data.mobile_app_links?.app_store
                            "
                            target="_blank"
                            class="topbar-link"
                        >
                            Tải ứng dụng
                        </a>
                        <span v-else class="topbar-text"> Tải ứng dụng </span>
                        <v-divider vertical class="mx-3 topbar-divider" />
                        <div class="d-flex align-center">
                            <span class="topbar-text me-2"> Kết nối </span>
                            <a
                                v-if="
                                    data.social_link &&
                                    data.social_link['facebook-f']
                                "
                                :href="data.social_link['facebook-f']"
                                target="_blank"
                                class="topbar-social-icon me-2"
                            >
                                <i class="lab la-facebook-f"></i>
                            </a>
                            <a v-else href="#" class="topbar-social-icon me-1">
                                <i class="lab la-facebook-f"></i>
                            </a>
                            <a
                                v-if="
                                    data.social_link &&
                                    data.social_link.instagram
                                "
                                :href="data.social_link.instagram"
                                target="_blank"
                                class="topbar-social-icon"
                            >
                                <i class="lab la-instagram"></i>
                            </a>
                            <a v-else href="#" class="topbar-social-icon">
                                <i class="lab la-instagram"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Spacer bên phải để cân bằng -->
                    <div class="topbar-language-container"></div>
                </div>
            </v-container>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import helpers from "../../utils/helpers";
export default {
    props: {
        loading: { type: Boolean, required: true, default: true },
        data: {
            type: Object,
            default: {},
        },
    },
    data: () => ({
        topBannerVisible: false,
        topBanner: {
            image: helpers.asset("/uploads/img/topbar.jpg"),
            link: "",
        },

        currencies: [
            {
                name: "U.S. Dollar",
                sysmbol: "$",
                code: "USD",
            },
            {
                name: "Taka",
                sysmbol: "Tk",
                code: "BDT",
            },
        ],
        cselectedCurrency: {
            name: "U.S. Dollar",
            sysmbol: "$",
            code: "USD",
        },
        menuCloseOnClick: true,
    }),
    computed: {
        ...mapGetters("app", ["generalSettings"]),
        ...mapGetters("wishlist", ["getTotalWishlisted"]),
        ...mapGetters("compareList", ["getTotalComparedList"]),
        ...mapGetters("app", [
            "userLanguageObj",
            "allLanguages",
            "allCurrencies",
        ]),
        ...mapGetters("auth", ["isAuthenticated"]),
    },
    methods: {
        ...mapActions("app", ["fetchProductQuerries"]),
        ...mapActions("wishlist", ["fetchWislistProducts"]),
        ...mapActions("app", ["setLanguage"]),

        switchLanguage(locale) {
            if (this.$i18n.locale !== locale) {
                this.setLanguage(locale);
                this.$i18n.locale = locale;
                window.location.reload();
            }
        },
        closeTopBanner() {
            this.topBannerVisible = false;
            this.setSession("shopTopBanner", "hidden");
        },
    },
    created() {
        if (this.checkSession("shopTopBanner") != "hidden") {
            this.topBannerVisible = true;
        }
        this.fetchWislistProducts();
        this.fetchProductQuerries();
        // setInterval(() => {
        //   this.fetchProductQuerries();
        // }, 8000);

        // set default language
        if (!localStorage.getItem("shopSelectedLanguage")) {
            localStorage.setItem(
                "shopSelectedLanguage",
                shopSetting.appLanguage
            );
            this.$i18n.locale = shopSetting.appLanguage;
        }
    },
};
</script>
<style scoped>
.topbar {
    position: relative;
    z-index: 2;
}

.topbar-bottom-band {
    width: 100%;
    background: var(
        --color-primary-gradient,
        linear-gradient(to right, #fdcc34, #ffd562)
    );
}

.topbar-link {
    color: #000 !important;
    text-decoration: none;
    font-weight: 400;
    font-size: 13px;
    transition: opacity 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
}

.topbar-link:hover {
    opacity: 0.8;
    text-decoration: underline;
}

.topbar-text {
    color: #000 !important;
    font-weight: 400;
    font-size: 13px;
    white-space: nowrap;
    cursor: default !important;
    text-decoration: none !important;
}

.topbar-text:hover {
    text-decoration: none !important;
}

.topbar-divider {
    border-color: rgba(0, 0, 0, 0.2) !important;
    height: 16px;
}

.topbar-social-icon {
    color: #000 !important;
    text-decoration: none;
    font-size: 16px;
    transition: opacity 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.topbar-social-icon:hover {
    opacity: 0.7;
}

.topbar-language-container {
    min-width: 120px;
    display: flex;
    align-items: center;
}

.topbar-language-switcher {
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.topbar-language-switcher:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Responsive cho mobile */
@media (max-width: 959px) {
    .topbar-bottom-band .d-flex {
        justify-content: center !important;
    }

    .topbar-language-container {
        display: none !important;
    }

    .topbar-bottom-band .flex-grow-1 {
        flex-grow: 1 !important;
        justify-content: center;
    }
}
</style>
