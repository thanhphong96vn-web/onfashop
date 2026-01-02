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

        <!-- Top bar: màu vàng với links bên trái và actions bên phải -->
        <div class="topbar-bottom-band">
            <v-container class="py-0 px-0">
                <div
                    class="d-flex align-center justify-between pt-2 pb-3 position-relative"
                >
                    <!-- Left side: Navigation links -->
                    <div class="d-flex align-center topbar-left-section">
                        <template v-if="is_addon_activated('multi_vendor')">
                            <router-link
                                :to="{ name: 'ShopRegistration' }"
                                class="topbar-link-left"
                            >
                                Kênh Người Bán
                            </router-link>
                        </template>
                        <template v-else>
                            <span class="topbar-text-left">
                                Kênh Người Bán
                            </span>
                        </template>
                        <div class="topbar-divider-vertical"></div>
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
                            class="topbar-link-left"
                        >
                            Tải ứng dụng
                        </a>
                        <span v-else class="topbar-text-left"> Tải ứng dụng </span>
                        <div class="topbar-divider-vertical"></div>
                        <div class="d-flex align-center">
                            <span class="topbar-text-left me-2 pr-1"> Kết nối </span>
                            <a
                                v-if="
                                    data.social_link &&
                                    data.social_link['facebook-f']
                                "
                                :href="data.social_link['facebook-f']"
                                target="_blank"
                                class="topbar-social-icon-circle me-2"
                            >
                                <i class="lab la-facebook-f"></i>
                            </a>
                            <a v-else href="#" class="topbar-social-icon-circle me-2">
                                <i class="lab la-facebook-f"></i>
                            </a>
                            <a
                                v-if="
                                    data.social_link &&
                                    data.social_link.instagram
                                "
                                :href="data.social_link.instagram"
                                target="_blank"
                                class="topbar-social-icon-circle"
                            >
                                <i class="lab la-instagram"></i>
                            </a>
                            <a v-else href="#" class="topbar-social-icon-circle">
                                <i class="lab la-instagram"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right side: User actions -->
                    <div class="d-flex align-center topbar-right-section">
                        <router-link
                            :to="{ name: 'Notification' }"
                            class="topbar-action-link me-3"
                        >
                            <i class="las la-bell fs-16 me-1"></i>
                            <span class="topbar-text">Thông Báo</span>
                        </router-link>
                        <a href="#" class="topbar-action-link me-3">
                            <i class="las la-question-circle fs-16 me-1"></i>
                            <span class="topbar-text">Hỗ Trợ</span>
                        </a>
                        <div
                            v-if="
                                allLanguages.length > 1 &&
                                data.show_language_switcher === 'on'
                            "
                            class="topbar-action-link me-3"
                        >
                            <v-menu offset-y>
                                <template v-slot:activator="{ props }">
                                    <div
                                        v-bind="props"
                                        class="d-flex align-center c-pointer"
                                    >
                                        <i class="las la-globe fs-16 me-1"></i>
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
                        <template v-if="!isAuthenticated">
                            <router-link
                                :to="{ name: 'Registration' }"
                                class="topbar-link me-3"
                            >
                                Đăng Ký
                            </router-link>
                            <router-link
                                :to="{ name: 'Login' }"
                                class="topbar-link"
                            >
                                Đăng Nhập
                            </router-link>
                        </template>
                        <!-- User dropdown when authenticated -->
                        <template v-else>
                            <div class="topbar-user-dropdown">
                                <v-menu offset-y open-on-hover>
                                    <template v-slot:activator="{ props }">
                                        <div
                                            v-bind="props"
                                            class="topbar-user-activator d-flex align-center c-pointer"
                                        >
                                            <i class="las la-user fs-18 me-1" style="margin-top: -2px; "></i>
                                            <span class="topbar-text">{{
                                                currentUser?.name || currentUser?.email || "User"
                                            }}</span>
                                        </div>
                                    </template>
                                    <v-list class="pa-0 user-dropdown-menu">
                                        <v-list-item
                                            :to="{ name: 'DashBoard' }"
                                            class="user-dropdown-item py-0"
                                            @click="closeUserMenu"
                                        >
                                            <v-list-item-title class="fs-14">
                                                Tài Khoản Của Tôi
                                            </v-list-item-title>
                                        </v-list-item>
                                        <v-list-item
                                            :to="{ name: 'PurchaseHistory' }"
                                            class="user-dropdown-item py-0"
                                            @click="closeUserMenu"
                                        >
                                            <v-list-item-title class="fs-14">
                                                Đơn Mua
                                            </v-list-item-title>
                                        </v-list-item>
                                        <v-divider class="my-1"></v-divider>
                                        <v-list-item
                                            @click="handleLogout"
                                            class="user-dropdown-item py-0"
                                        >
                                            <v-list-item-title class="fs-14">
                                                Đăng Xuất
                                            </v-list-item-title>
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                            </div>
                        </template>
                    </div>
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
        ...mapGetters("auth", ["isAuthenticated", "currentUser"]),
    },
    methods: {
        ...mapActions("app", ["fetchProductQuerries"]),
        ...mapActions("wishlist", ["fetchWislistProducts"]),
        ...mapActions("app", ["setLanguage"]),
        ...mapActions(["auth/logout"]),
        ...mapActions("cart", ["resetCart"]),
        ...mapActions("wishlist", ["resetWishlist"]),

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
        closeUserMenu() {
            // Menu will close automatically when navigating
        },
        async handleLogout() {
            const res = await this.call_api("get", "auth/logout");
            this["auth/logout"]();
            this.resetCart();
            this.resetWishlist();
            this.$router.push({ name: "Home" }).catch(() => {});
        },
    },
    created() {
        if (this.checkSession("shopTopBanner") != "hidden") {
            this.topBannerVisible = true;
        }
        this.fetchWislistProducts();
        // Only fetch conversations if user is authenticated
        // SellerChat component will handle its own refresh interval
        if (this.isAuthenticated) {
            this.fetchProductQuerries();
        }
        // Commented out: No need for interval here, SellerChat handles it
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
    background: var(--color-primary-gradient, linear-gradient(to right, #fdcc34, #ffd562));
}

.topbar-bottom-band {
    width: 100%;
}

.topbar-link {
    color: #000 !important;
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: opacity 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
}

.topbar-link:hover {
    opacity: 0.8;
    text-decoration: underline;
}

.topbar-link-left {
    text-decoration: none;
    font-weight: 500;
    font-size: 13px;
    transition: opacity 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    padding: 0 12px;
    color: #000;
}

.topbar-link-left:hover {
    opacity: 0.9;
    text-decoration: none;
}

.topbar-text {
    color: #000 !important;
    font-weight: 500;
    font-size: 13px;
    white-space: nowrap;
    text-decoration: none !important;
}

.topbar-text:hover {
    text-decoration: none !important;
}

.topbar-text-left {
    font-weight: 500;
    font-size: 13px;
    white-space: nowrap;
    text-decoration: none !important;
    padding: 0 12px;
}

.topbar-text-left:hover {
    text-decoration: none !important;
}

.topbar-divider {
    border-color: rgba(0, 0, 0, 0.2) !important;
    height: 16px;
}

.topbar-divider-vertical {
    width: 1px;
    height: 16px;
    background-color: rgba(255, 255, 255, 0.3);
    margin: 0 4px;
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

.topbar-social-icon-circle {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: opacity 0.2s ease;
    margin-left: 4px;
    margin-top: -4px;
}

.topbar-social-icon-circle i {
    color: #000;
    font-size: 12px;
}

.topbar-social-icon-circle:last-child i {
    font-size: 16px;
}

.topbar-social-icon-circle:hover {
    opacity: 0.8;
}

.topbar-left-section {
    flex: 1;
    display: flex;
    align-items: center;
}

.topbar-right-section {
    display: flex;
    align-items: center;
}

.topbar-action-link {
    color: #000 !important;
    text-decoration: none;
    font-weight: 400;
    font-size: 13px;
    transition: opacity 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.topbar-action-link:hover {
    opacity: 0.8;
}

.topbar-user-dropdown {
    position: relative;
}

.topbar-user-activator {
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.topbar-user-activator:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.user-dropdown-menu {
    background: #fff !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    border-radius: 4px;
    min-width: 170px;
    margin-top: 8px;
    padding: 10px 0 !important;
}

.user-dropdown-item {
    padding: 8px 16px !important;
    cursor: pointer;
    transition: background-color 0.2s ease;
    max-height: 30px !important;
}

.user-dropdown-item:hover {
    background-color: #f5f5f5 !important;
}

.user-dropdown-item :deep(.v-list-item-title) {
    color: #000 !important;
    font-weight: 400;
    line-height: 1.5;
}

/* Override Vuetify v-list-item styles */
.user-dropdown-menu :deep(.v-list-item--one-line) {
    min-height: 30px !important;
    padding-top: 0px !important;
    padding-bottom: 0px !important;
}

.user-dropdown-menu :deep(.v-list-item--density-default.v-list-item--one-line) {
    min-height: 30px !important;
    padding-top: 0px !important;
    padding-bottom: 0px !important;
}

.user-dropdown-item :deep(.v-divider) {
    margin: 2px 0 !important;
    border-color: rgba(0, 0, 0, 0.12) !important;
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
    .topbar {
        display: none !important;
    }
}
</style>
