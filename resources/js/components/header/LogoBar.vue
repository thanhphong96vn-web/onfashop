<template>
    <div class="logobar">
        <v-container class="pb-md-0 pt-0 px-0">
            <div class="d-flex align-center">
                <!-- Left: Logo -->
                <div class="logo me-4">
                    <router-link :to="{ name: 'Home' }" class="d-block lh-0">
                        <img 
                            :src="getImageUrl(appLogo)" 
                            :alt="appName" 
                            height="30"
                            @error="imageFallback($event)"
                        />
                    </router-link>
                </div>
                
                <!-- Center: Search Box -->
                <div :class="['flex-grow-1 search-box px-6', { open: openSearch }]">
                    <v-form
                        class="search-form flex-grow-1"
                        @submit.stop.prevent="search()"
                    >
                        <div class="d-flex align-center search-form-wrapper">
                            <v-col cols="auto ms-1" class="d-md-none pa-0">
                                <v-btn
                                    icon
                                    @click.stop="toggleSearch(false)"
                                    style="box-shadow: none"
                                >
                                    <i
                                        class="las la-arrow-left fs-18 ts-05"
                                    ></i>
                                </v-btn>
                            </v-col>
                            <div class="flex-grow-1 search-input-wrapper position-relative">
                                <v-text-field
                                    :placeholder="
                                        $t(
                                            'search_for_products_brands_and_more'
                                        )
                                    "
                                    type="text"
                                    hide-details="auto"
                                    class="search-input"
                                    v-model="searchKeyword"
                                    @keyup.enter="search()"
                                    @keyup="ajaxSearch"
                                    required
                                    variant="plain"
                                ></v-text-field>
                                <v-btn
                                    type="submit"
                                    class="search-submit-btn"
                                    @click.stop="search()"
                                >
                                    <i class="las la-search fs-18"></i>
                                </v-btn>
                            </div>
                        </div>
                    </v-form>
                    <div
                        class="bg-white shadow-lg position-absolute search_content_box"
                        v-if="showSuggestionContainer"
                    >
                        <div class="text-center py-4" v-if="loadingSuggestion">
                            <v-progress-circular
                                indeterminate
                                :width="3"
                                color="primary"
                            ></v-progress-circular>
                        </div>

                        <div v-else>
                            <div
                                v-if="suggestionNotFound"
                                class="text-center ma-8 fs-16"
                            >
                                {{ getTranslation("sorry_nothing_found", "Xin lỗi, không tìm thấy gì") }}
                            </div>
                            <div class="search_content" v-else>
                                <!-- Tags -->
                                <div class="" v-if="keywords.length">
                                    <div
                                        class="px-2 py-1 text-uppercase fs-10 text-right bg-grey-lighten-3"
                                    >
                                        {{ getTranslation("popular_suggestions", "Gợi ý phổ biến") }}
                                    </div>
                                    <ul class="list-unstyled px-5 py-2 fs-13">
                                        <li
                                            v-for="(keyword, i) in keywords"
                                            :key="i"
                                            class="py-1 text-capitalize"
                                            @click="popularSuggesation(keyword)"
                                        >
                                            {{ keyword }}
                                        </li>
                                    </ul>
                                </div>

                                <!-- Product Suggesations -->
                                <div class="" v-if="products.length">
                                    <div
                                        class="px-2 py-1 text-uppercase fs-10 text-right bg-grey-lighten-3"
                                    >
                                        {{ getTranslation("products", "Sản phẩm") }}
                                    </div>
                                    <ul class="list-unstyled px-5 py-2 fs-13">
                                        <li
                                            v-for="(product, i) in products"
                                            :key="i"
                                            class="py-1 d-flex align-center"
                                        >
                                            <img
                                                :src="product.thumbnail_image || static_asset('/assets/img/placeholder.jpg')"
                                                :alt="product.name"
                                                @error="imageFallback($event)"
                                                class="img-fit size-50px"
                                            />
                                            <div class="ml-2">
                                                <h5
                                                    class="opacity-60 mb-1 fs-13"
                                                    @click="hideSearchContainer"
                                                >
                                                    <router-link
                                                        :to="{
                                                            name: 'ProductDetails',
                                                            params: {
                                                                slug: product.slug,
                                                            },
                                                        }"
                                                        class="text-reset"
                                                    >
                                                        {{ product.name }}
                                                    </router-link>
                                                </h5>
                                                <div class="order-2 fs-14 lh-1">
                                                    <template
                                                        v-if="
                                                            product.base_price >
                                                            product.base_discounted_price
                                                        "
                                                    >
                                                        <del
                                                            class="opacity-40"
                                                            >{{
                                                                format_price(
                                                                    product.base_price
                                                                )
                                                            }}</del
                                                        >
                                                        <span
                                                            class="fw-700 text-red"
                                                            >{{
                                                                format_price(
                                                                    product.base_discounted_price
                                                                )
                                                            }}</span
                                                        >
                                                    </template>
                                                    <template v-else>
                                                        <span
                                                            class="fw-700 text-red"
                                                            >{{
                                                                format_price(
                                                                    product.base_discounted_price
                                                                )
                                                            }}</span
                                                        >
                                                    </template>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                <!-- category Suggesations -->
                                <div class="" v-if="categories.length">
                                    <div
                                        class="px-2 py-1 text-uppercase fs-10 text-right bg-grey-lighten-3"
                                    >
                                        {{ getTranslation("category_suggestions", "Gợi ý danh mục") }}
                                    </div>
                                    <ul class="list-unstyled px-5 fs-13">
                                        <li
                                            v-for="(category, i) in categories"
                                            :key="i"
                                            class="py-1"
                                            @click="hideSearchContainer"
                                        >
                                            <router-link
                                                :to="{
                                                    name: 'Category',
                                                    params: {
                                                        categorySlug:
                                                            category.slug,
                                                    },
                                                }"
                                                class="text-reset text-capitalize"
                                                >{{
                                                    category.name
                                                }}</router-link
                                            >
                                        </li>
                                    </ul>
                                </div>

                                <!-- Brand Suggesations -->
                                <div class="" v-if="brands.length">
                                    <div
                                        class="px-2 py-1 text-uppercase fs-10 text-right bg-grey-lighten-3"
                                    >
                                        {{ getTranslation("brands", "Thương hiệu") }}
                                    </div>
                                    <ul class="list-unstyled px-5 fs-13">
                                        <li
                                            v-for="(brand, i) in brands"
                                            :key="i"
                                            class="py-1"
                                            @click="hideSearchContainer"
                                        >
                                            <router-link
                                                :to="{
                                                    name: 'Brand',
                                                    params: {
                                                        brandId: brand.id,
                                                    },
                                                }"
                                                class="text-reset text-capitalize"
                                                >{{ brand.name }}</router-link
                                            >
                                        </li>
                                    </ul>
                                </div>

                                <!-- Shop Suggesations -->
                                <div class="" v-if="shops.length">
                                    <div
                                        class="px-2 py-1 text-uppercase fs-10 text-right bg-grey-lighten-3"
                                    >
                                        {{ getTranslation("Shops", "Cửa hàng") }}
                                    </div>
                                    <ul class="list-unstyled px-5 py-2 fs-13">
                                        <li
                                            v-for="(shop, i) in shops"
                                            :key="i"
                                            class="py-1 d-flex align-center"
                                        >
                                            <img
                                                :src="shop.logo || static_asset('/assets/img/placeholder.jpg')"
                                                :alt="shop.name"
                                                @error="imageFallback($event)"
                                                class="img-fit size-30px"
                                            />
                                            <div class="ml-3">
                                                <h5
                                                    class="opacity-60 mb-1 fs-13"
                                                    @click="hideSearchContainer"
                                                >
                                                    <router-link
                                                        :to="{
                                                            name: 'ShopDetails',
                                                            params: {
                                                                slug: shop.slug,
                                                            },
                                                        }"
                                                        class="text-reset"
                                                    >
                                                        {{ shop.name }}
                                                    </router-link>
                                                </h5>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    <v-btn
                        style="height: 40px; width: 40px; min-width: 0 !important"
                        class="d-md-none border-gray-300 rounded-circle mobile-search-button"
                        fab
                        variant="outlined"
                        small
                        @click.stop="toggleSearch(true)"
                        elevation="0"
                    >
                        <i class="las la-search fs-18 ts-05"></i>
                    </v-btn>

                <!-- Right: Shopping Cart -->
                <div class="d-none d-md-flex align-center ms-4">
                    <router-link
                        :to="{ name: 'Cart' }"
                        class="header-cart-link position-relative"
                    >
                        <i class="las la-shopping-cart fs-30"></i>
                        <span
                            v-if="getCartCount > 0"
                            class="absolute-top-right red size-18px fs-10 d-flex align-center justify-center rounded-circle white--text fw-600 bg-white"
                            style="min-width: 18px; padding: 0 4px;"
                        >{{ getCartCount }}</span>
                    </router-link>
                </div>
            </div>
        </v-container>
    </div>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from "vuex";
export default {
    props: {
        loading: { type: Boolean, required: true, default: true },
        data: {
            type: Object,
            default: {},
        },
    },
    data: () => ({
        openSearch: false,
        searchKeyword: "",

        loadingSuggestion: false,
        showSuggestionContainer: false,
        suggestionNotFound: false,
        keywords: [],
        categories: [],
        brands: [],
        products: [],
        shops: [],
        notifications: [],
    }),
    computed: {
        ...mapGetters("app", ["appLogo", "appName", "generalSettings"]),
        ...mapGetters("auth", ["isAuthenticated", "currentUser"]),
        ...mapGetters("cart", ["getCartCount"]),
    },
    methods: {
        getTranslation(key, fallback) {
            const translated = this.$t(key);
            // If translation returns the same key, it means translation not found
            if (translated === key || !translated) {
                return fallback;
            }
            return translated;
        },
        getImageUrl(url) {
            // If URL is empty, null, undefined, or just whitespace, use fallback
            if (!url || url.trim() === '' || url === 'null' || url === 'undefined') {
                return this.static_asset('/assets/img/logo.png');
            }
            // If URL is already a full URL (starts with http:// or https://), return as is
            if (url.startsWith('http://') || url.startsWith('https://')) {
                return url;
            }
            // Otherwise, use the URL as is (it might be a relative path from API)
            return url;
        },
        imageFallback(event) {
            const placeholder = this.static_asset('/assets/img/placeholder.jpg');
            const logoPlaceholder = this.static_asset('/assets/img/logo.png');
            // Try logo placeholder first, then generic placeholder
            if (event.target.src !== logoPlaceholder && !event.target.src.includes(logoPlaceholder)) {
                if (event.target.alt && event.target.alt.includes('Shop')) {
                    event.target.src = logoPlaceholder;
                } else {
                    event.target.src = placeholder;
                }
            }
        },
        ...mapActions(["auth/logout"]),
        ...mapActions("cart", ["resetCart", "fetchCartProducts"]),
        ...mapActions("wishlist", ["resetWishlist"]),
        ...mapMutations("auth", ["updateCartDrawer"]),

        openOrderDetails(orderCode) {
            this.$router.push({
                name: "OrderDetails",
                params: { code: orderCode },
            });
        },
        async fetNotification() {
            const res = await this.call_api("get", `user/notification`);
            if (res.data.success) {
                this.notifications = res.data.notifications;
            }
        },

        search() {
            this.showSuggestionContainer = false;
            this.$router
                .push({
                    name: "Search",
                    params:
                        this.searchKeyword.length > 0
                            ? { keyword: this.searchKeyword }
                            : {},
                    query: {
                        page: 1,
                    },
                })
                .catch(() => {});
        },

        hideSearchContainer() {
            this.showSuggestionContainer = false;
        },

        popularSuggesation(tag) {
            this.showSuggestionContainer = false;
            this.searchKeyword = tag;
            this.search();
        },

        async ajaxSearch(event) {
            this.loadingSuggestion = true;
            this.showSuggestionContainer = false;
            const searchKey = event.target.value;

            if (searchKey.length > 0) {
                this.showSuggestionContainer = true;
                const res = await this.call_api(
                    "get",
                    `search.ajax/${searchKey}`
                );

                if (res.data.success) {
                    this.suggestionNotFound = false;
                    this.loadingSuggestion = false;
                    this.keywords = res.data.keywords;
                    this.categories = res.data.categories;
                    this.brands = res.data.brands;
                    this.products = res.data.products.data;
                    this.shops = res.data.shops.data;
                } else {
                    this.loadingSuggestion = false;
                    this.suggestionNotFound = true;
                }
            }
        },

        async logout() {
            const res = await this.call_api("get", "auth/logout");
            this["auth/logout"]();
            this.resetCart();
            this.resetWishlist();
            this.$router.push({ name: "Home" }).catch(() => {});
        },
        toggleSearch(status) {
            this.openSearch = status;
        },
        openCartDrawer() {
            this.updateCartDrawer(true);
        },
        // Close search content bar when click
        onClick: function (event) {
            let trigger = document.getElementsByClassName(
                ".search_content_box"
            );
            if (trigger !== event.target) {
                this.showSuggestionContainer = false;
            }
        },
    },
    created() {
        // Fetch cart products when component is created
        this.fetchCartProducts();
    },
    mounted() {
        document.addEventListener("click", this.onClick);
    },
};
</script>
<style scoped>
.logobar {
    position: relative;
    z-index: 1;
    background: var(
        --color-primary-gradient,
        linear-gradient(to right, #fdcc34, #ffd562)
    );
}

.logo {
    flex-shrink: 0;
}

.header-cart-link {
    color: #000 !important;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    transition: opacity 0.2s ease;
    position: relative;
}

.header-cart-link:hover {
    opacity: 0.8;
}

.header-divider {
    border-color: rgba(0, 0, 0, 0.15) !important;
    height: 40px;
}

.search-icon-clickable {
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.search-icon-clickable:hover {
    opacity: 0.7;
}

.header-login-btn {
    background-color: #fff3cd;
    border: 1px solid #ffd700;
    border-radius: 4px;
    padding: 8px 16px;
    color: #000 !important;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
}

.header-login-btn:hover {
    background-color: #ffe69c;
    opacity: 1;
}

.search-form {
    background: #fff;
    border: none;
    box-shadow: none;
    padding: 0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    max-width: 100%;
    overflow: hidden;
}

.search-form-wrapper {
    width: 100%;
    display: flex;
    align-items: stretch;
    gap: 0;
}

.search-input-wrapper {
    flex: 1;
    min-width: 0;
    position: relative;
}

.search-input-wrapper :deep(.v-field) {
    border-radius: 4px !important;
    overflow: visible !important;
}

.search-input :deep(.v-field__input) {
    padding-left: 16px;
    padding-right: 55px;
    padding-top: 8px !important;
    padding-bottom: 8px !important;
    min-height: 40px !important;
    font-size: 14px;
}

.search-input :deep(.v-field) {
    min-height: 40px !important;
    box-shadow: none;
    border-radius: 4px !important;
    overflow: visible !important;
}

.search-input :deep(.v-field__field) {
    min-height: 40px !important;
    border-radius: 4px !important;
    overflow: visible !important;
}

.search-input :deep(.v-field__overlay) {
    border-radius: 4px !important;
}

.search-submit-btn {
    position: absolute;
    right: 2px;
    top: 50%;
    transform: translateY(-50%);
    background: var(
        --color-primary-gradient,
        linear-gradient(to right, #fdcc34, #ffd562)
    ) !important;
    color: #fff !important;
    min-width: 46px !important;
    width: 46px !important;
    height: 36px !important;
    border-radius: 4px !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 2;
}

.search-submit-btn:hover {
    opacity: 0.9;
}

.search-submit-btn:active {
    opacity: 0.85;
}

.search-submit-btn i {
    color: #fff !important;
}
.search_content_box {
    width: 100%;
}

@media (max-width: 768px) {
    .search_content_box {
        top: 60px;
    }
}
@media (max-width: 959px) {
    .logobar {
        padding-top: 16px;
    }

    .logobar .d-flex {
        justify-content: space-between;
    }
    
    .logo img {
        max-height: 26px !important;
        height: auto !important;
        width: auto !important;
    }
    
    .mobile-search-button {
        margin-left: auto !important;
        margin-right: 10px !important;
    }
    
    .search-box {
        position: absolute;
        width: calc(100% - 24px);
        padding: 9px 0;
        height: 100%;
        left: 12px;
        right: 12px;
        top: -100%;
        background: #fff;
        display: flex;
        align-items: center;
        z-index: 3;
        transition: top 0.3s;
        -webkit-transition: top 0.3s;
    }
    .search-box.open {
        top: 0px;
    }
}
@media (min-width: 960px) {
    .search-box {
        position: relative;
    }
}

.v-menu__content.theme-light.menuable__content__active {
    top: 100px !important;
    position: fixed;
    min-width: 350px !important;
}

.notifications-menu {
    min-height: 100px;
    max-height: 300px;
    overflow-y: auto;
}
</style>
