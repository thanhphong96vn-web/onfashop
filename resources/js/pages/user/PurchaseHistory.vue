<template>
    <div class="ps-lg-7 pt-4 purchase-history-shopee">
        <h1 class="fs-24 fw-700 opacity-80 mb-4 mt-3">
            {{ $t("purchase_history") }}
        </h1>

        <!-- Search Bar - Shopee Style -->
        <div class="search-bar mb-4">
            <v-text-field
                v-model="searchQuery"
                :placeholder="$t('search_by_order_code_product_or_shop')"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="compact"
                hide-details
                clearable
                @keyup.enter="searchOrders"
                @click:clear="clearSearch"
                class="search-input"
            ></v-text-field>
        </div>

        <!-- Tabs - Shopee Style -->
        <div class="order-tabs-wrapper">
            <v-tabs
                v-model="activeTab"
                class="order-tabs"
                color="primary"
                slider-color="primary"
                @update:modelValue="onTabChange"
            >
                <v-tab
                    v-for="tab in tabs"
                    :key="tab.value"
                    :value="tab.value"
                    class="tab-item"
                >
                    {{ tab.label }}
                    <span v-if="tab.count > 0" class="tab-count"
                        >({{ tab.count }})</span
                    >
                </v-tab>
            </v-tabs>
        </div>

        <!-- Orders List - Card View Shopee Style -->
        <div class="orders-container">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
                <v-progress-circular
                    indeterminate
                    color="primary"
                    size="48"
                ></v-progress-circular>
                <p class="mt-4 text-grey">
                    {{ $t("loading_please_wait") }}...
                </p>
            </div>

            <!-- Empty State -->
            <div
                v-else-if="!loading && orders.length === 0"
                class="empty-state"
            >
                <i class="las la-shopping-bag empty-icon"></i>
                <p class="empty-text">{{ $t("no_orders_found") }}</p>
                <v-btn
                    color="primary"
                    variant="flat"
                    @click="$router.push({ name: 'Home' })"
                    class="mt-3"
                >
                    {{ $t("start_shopping") }}
                </v-btn>
            </div>

            <!-- Order Cards -->
            <div v-else>
                <div
                    v-for="order in orders"
                    :key="order.code"
                    class="order-card"
                >
                    <!-- Card Header -->
                    <div class="order-card-header">
                        <div class="d-flex align-center justify-space-between">
                            <div class="d-flex align-center gap-3">
                                <i class="las la-store fs-18 text-grey"></i>
                                <span class="fw-600 fs-15">{{
                                    order.code
                                }}</span>
                                <span class="order-divider">|</span>
                                <span class="text-grey fs-13">{{
                                    order.date
                                }}</span>
                            </div>
                            <div class="d-flex align-center gap-2">
                                <span
                                    :class="
                                        getStatusClass(order.delivery_status)
                                    "
                                    class="status-badge"
                                >
                                    {{ getStatusLabel(order.delivery_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body - Products -->
                    <div class="order-card-body">
                        <div
                            v-for="(shopOrder, idx) in order.orders"
                            :key="idx"
                            class="shop-section"
                        >
                            <!-- Shop Name -->
                            <div
                                v-if="is_addon_activated('multi_vendor')"
                                class="shop-header"
                            >
                                <router-link
                                    :to="{
                                        name: 'ShopDetails',
                                        params: { slug: shopOrder.shop.slug },
                                    }"
                                    class="shop-name"
                                >
                                    <i class="las la-store me-1"></i>
                                    {{ shopOrder.shop.name }}
                                </router-link>
                                <v-btn
                                    variant="outlined"
                                    size="small"
                                    class="chat-btn"
                                    @click.stop="chatWithShop(shopOrder.shop)"
                                >
                                    <i class="las la-comment me-1"></i>
                                    {{ $t("chat") }}
                                </v-btn>
                            </div>

                            <!-- Products List -->
                            <div
                                v-for="product in shopOrder.products.data.slice(
                                    0,
                                    3
                                )"
                                :key="product.id"
                                class="product-item"
                                @click="openOrderDetails(order)"
                            >
                                <img
                                    :src="product.thumbnail"
                                    :alt="product.name"
                                    @error="imageFallback($event)"
                                    class="product-image"
                                />
                                <div class="product-info">
                                    <div class="product-name">
                                        {{ product.name }}
                                    </div>
                                    <div
                                        class="product-variation"
                                        v-if="product.combinations.length > 0"
                                    >
                                        <span
                                            v-for="(
                                                combination, j
                                            ) in product.combinations"
                                            :key="j"
                                        >
                                            {{ combination.attribute }}:
                                            {{ combination.value
                                            }}<span
                                                v-if="
                                                    j <
                                                    product.combinations
                                                        .length -
                                                        1
                                                "
                                                >,
                                            </span>
                                        </span>
                                    </div>
                                    <div class="product-qty">
                                        x{{ product.quantity }}
                                    </div>
                                </div>
                                <div class="product-price">
                                    {{ format_price(product.price) }}
                                </div>
                            </div>

                            <!-- Show More Products -->
                            <div
                                v-if="shopOrder.products.data.length > 3"
                                class="more-products"
                                @click="openOrderDetails(order)"
                            >
                                +{{ shopOrder.products.data.length - 3 }}
                                {{ $t("more_products") }}
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="order-card-footer">
                        <div class="d-flex align-center justify-space-between">
                            <div class="order-info">
                                <span class="text-grey fs-13">
                                    {{ getProductsCount(order) }}
                                    {{ $t("products") }}
                                </span>
                                <span
                                    v-if="is_addon_activated('multi_vendor')"
                                    class="text-grey fs-13 ms-3"
                                >
                                    {{ $t("from") }} {{ order.orders.length }}
                                    {{ $t("shops") }}
                                </span>
                            </div>
                            <div class="d-flex align-center gap-3">
                                <div class="total-amount">
                                    <span class="text-grey fs-13 me-2">{{
                                        $t("total")
                                    }}:</span>
                                    <span class="amount-value">{{
                                        format_price(order.grand_total)
                                    }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons mt-3">
                            <v-btn
                                variant="outlined"
                                size="small"
                                @click.stop="invoiceDownload(order)"
                                class="action-btn"
                            >
                                <i class="las la-download me-1"></i>
                                {{ $t("invoice") }}
                            </v-btn>
                            <v-btn
                                variant="outlined"
                                size="small"
                                color="primary"
                                @click.stop="reOrder(order.orders)"
                                class="action-btn"
                            >
                                <i class="las la-sync me-1"></i>
                                {{ $t("reorder") }}
                            </v-btn>
                            <v-btn
                                variant="flat"
                                size="small"
                                color="primary"
                                @click.stop="openOrderDetails(order)"
                                class="action-btn"
                            >
                                {{ $t("view_details") }}
                            </v-btn>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="text-center mt-6" v-if="totalPages > 1">
                <v-pagination
                    v-model="currentPage"
                    @update:modelValue="getList"
                    :length="totalPages"
                    prev-icon="las la-angle-left"
                    next-icon="las la-angle-right"
                    :total-visible="7"
                    elevation="0"
                    class="custom-pagination"
                ></v-pagination>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapMutations } from "vuex";

export default {
    data: () => ({
        loading: true,
        currentPage: 1,
        totalPages: 1,
        orders: [],
        selectedOrder: {},
        activeTab: "all",
        searchQuery: "",
        tabs: [
            { label: "All", value: "all", count: 0 },
            { label: "To Pay", value: "unpaid", count: 0 },
            { label: "To Ship", value: "pending", count: 0 },
            { label: "To Receive", value: "shipping", count: 0 },
            { label: "Completed", value: "delivered", count: 0 },
            { label: "Cancelled", value: "cancelled", count: 0 },
            { label: "Return/Refund", value: "refund", count: 0 },
        ],
        deliveryStatusMap: {
            all: null,
            unpaid: "unpaid",
            pending: "order_placed",
            shipping: "on_delivery",
            delivered: "delivered",
            cancelled: "cancelled",
            refund: "refund_requested",
        },
    }),
    computed: {},
    watch: {
        currentPage() {
            this.$router
                .push({
                    query: {
                        ...this.$route.query,
                        page: this.currentPage,
                    },
                })
                .catch(() => {});
        },
        activeTab() {
            this.$router
                .push({
                    query: {
                        ...this.$route.query,
                        status: this.activeTab,
                        page: 1,
                    },
                })
                .catch(() => {});
        },
    },
    methods: {
        ...mapActions("cart", ["addToCart"]),
        ...mapMutations("auth", ["updateCartDrawer"]),

        getProductsCount(combinedOrder) {
            let count = 0;
            combinedOrder.orders.forEach((order) => {
                count += order.products.data.length;
            });
            return count;
        },

        async getList() {
            this.loading = true;

            // Build query params
            let params = `page=${this.currentPage}`;

            // Add status filter
            const status = this.deliveryStatusMap[this.activeTab];
            if (status) {
                params += `&delivery_status=${status}`;
            }

            // Add search query
            if (this.searchQuery) {
                params += `&search=${encodeURIComponent(this.searchQuery)}`;
            }

            const res = await this.call_api("get", `user/orders?${params}`);

            if (res.data.success) {
                this.orders = res.data.data;
                this.totalPages = res.data.meta.last_page;
                this.currentPage = res.data.meta.current_page;

                // Update tab counts if available
                if (res.data.counts) {
                    this.updateTabCounts(res.data.counts);
                }
            } else {
                this.snack({
                    message: this.$i18n.t("something_went_wrong"),
                    color: "red",
                });
            }
            this.loading = false;
        },

        updateTabCounts(counts) {
            this.tabs.forEach((tab) => {
                if (counts[tab.value] !== undefined) {
                    tab.count = counts[tab.value];
                }
            });
        },

        onTabChange(newTab) {
            this.activeTab = newTab;
            this.currentPage = 1;
            this.getList();
        },

        searchOrders() {
            this.currentPage = 1;
            this.getList();
        },

        clearSearch() {
            this.searchQuery = "";
            this.currentPage = 1;
            this.getList();
        },

        getStatusLabel(deliveryStatus) {
            const statusLabels = {
                order_placed: this.$i18n.t("order_placed") || "To Ship",
                confirmed: this.$i18n.t("confirmed") || "Confirmed",
                on_delivery: this.$i18n.t("on_delivery") || "Shipping",
                delivered: this.$i18n.t("delivered") || "Completed",
                cancelled: this.$i18n.t("cancelled") || "Cancelled",
                refund_requested:
                    this.$i18n.t("refund_requested") || "Refund Requested",
            };
            return statusLabels[deliveryStatus] || deliveryStatus;
        },

        getStatusClass(deliveryStatus) {
            const statusClasses = {
                order_placed: "status-pending",
                confirmed: "status-confirmed",
                on_delivery: "status-shipping",
                delivered: "status-completed",
                cancelled: "status-cancelled",
                refund_requested: "status-refund",
            };
            return statusClasses[deliveryStatus] || "status-default";
        },

        chatWithShop(shop) {
            // Implement chat functionality
            this.snack({
                message: this.$i18n.t("chat_feature_coming_soon"),
                color: "info",
            });
            // TODO: Navigate to chat page or open chat dialog
            // this.$router.push({ name: 'Chat', params: { shopId: shop.id } });
        },

        openOrderDetails(order) {
            this.$router.push({
                name: "OrderDetails",
                params: { code: order.code },
            });
        },

        async reOrder(orders) {
            orders.forEach((order) => {
                this.multipleShop(order);
            });
        },

        multipleShop(order) {
            order.products.data.forEach((product) => {
                this.addToCart({
                    variation_id: product.product_variation_id,
                    qty: product.quantity,
                });
            });
            this.checkout();
        },

        checkout() {
            this.$router.push({ name: "Checkout" }).catch((e) => {
                if (this.$route.name == "Checkout") {
                    this.updateCartDrawer(false);
                }
            });
        },

        async invoiceDownload(order) {
            order.orders.forEach(async (order) => {
                const res = await this.call_api(
                    "get",
                    `order/invoice-download/${order.id}`
                );
                if (res.data.success) {
                    const fileUrl = res.data.invoice_url;
                    const link = document.createElement("a");
                    link.href = fileUrl;
                    link.download = res.data.invoice_name;
                    link.click();
                } else {
                    this.snack({
                        message: this.$i18n.t("something_went_wrong"),
                        color: "red",
                    });
                }
            });
        },
    },

    created() {
        // Get status from query params
        const statusFromQuery = this.$route.query.status;
        if (statusFromQuery && this.deliveryStatusMap[statusFromQuery]) {
            this.activeTab = statusFromQuery;
        }

        // Get page from query params
        const pageFromQuery = this.$route.query.page;
        if (pageFromQuery) {
            this.currentPage = parseInt(pageFromQuery);
        }

        this.getList();
    },
};
</script>

<style scoped>
/* Shopee Style - Purchase History */
.purchase-history-shopee {
    max-width: 1200px;
}

/* Search Bar */
.search-bar {
    background: white;
    border-radius: 4px;
}

.search-input :deep(.v-field) {
    border-radius: 4px;
    background: #f5f5f5;
}

.search-input :deep(.v-field__input) {
    padding: 8px 12px;
    font-size: 14px;
}

/* Tabs - Shopee Style */
.order-tabs-wrapper {
    background: white;
    border-radius: 4px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 64px;
    z-index: 10;
}

.order-tabs :deep(.v-tab) {
    text-transform: none;
    font-size: 14px;
    font-weight: 500;
    color: #555;
    letter-spacing: 0;
    min-width: 100px;
    padding: 16px 20px;
}

.order-tabs :deep(.v-tab--selected) {
    color: #ee4d2d;
}

.order-tabs :deep(.v-tabs-slider) {
    background-color: #ee4d2d;
    height: 3px;
}

.tab-count {
    margin-left: 4px;
    font-size: 13px;
    color: #999;
}

.order-tabs :deep(.v-tab--selected) .tab-count {
    color: #ee4d2d;
}

/* Orders Container */
.orders-container {
    min-height: 400px;
}

/* Order Card - Shopee Style */
.order-card {
    background: white;
    border-radius: 4px;
    margin-bottom: 16px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
}

.order-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Card Header */
.order-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.order-divider {
    color: #e0e0e0;
    margin: 0 8px;
}

/* Status Badge */
.status-badge {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
}

.status-pending {
    background: #fff7e6;
    color: #fa8c16;
}

.status-confirmed {
    background: #e6f7ff;
    color: #1890ff;
}

.status-shipping {
    background: #f0f5ff;
    color: #2f54eb;
}

.status-completed {
    background: #f6ffed;
    color: #52c41a;
}

.status-cancelled {
    background: #fff1f0;
    color: #ff4d4f;
}

.status-refund {
    background: #fff0f6;
    color: #eb2f96;
}

.status-default {
    background: #f5f5f5;
    color: #999;
}

/* Card Body */
.order-card-body {
    padding: 0;
}

.shop-section {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.shop-section:last-child {
    border-bottom: none;
}

.shop-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f5f5f5;
}

.shop-name {
    color: #333;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
}

.shop-name:hover {
    color: #ee4d2d;
}

.chat-btn {
    border-color: #ee4d2d !important;
    color: #ee4d2d !important;
    text-transform: none;
    font-size: 13px;
}

.chat-btn:hover {
    background: #fff5f5 !important;
}

/* Product Item */
.product-item {
    display: flex;
    align-items: center;
    padding: 12px 0;
    cursor: pointer;
    transition: background 0.2s;
}

.product-item:hover {
    background: #fafafa;
    margin: 0 -20px;
    padding-left: 20px;
    padding-right: 20px;
}

.product-image {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #f0f0f0;
    flex-shrink: 0;
}

.product-info {
    flex: 1;
    margin-left: 12px;
    min-width: 0;
}

.product-name {
    font-size: 14px;
    color: #333;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
}

.product-variation {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

.product-qty {
    font-size: 13px;
    color: #666;
    margin-top: 4px;
}

.product-price {
    font-size: 15px;
    font-weight: 600;
    color: #333;
    margin-left: 16px;
    flex-shrink: 0;
}

.more-products {
    padding: 8px 12px;
    background: #f5f5f5;
    border-radius: 4px;
    font-size: 13px;
    color: #666;
    text-align: center;
    cursor: pointer;
    margin-top: 8px;
}

.more-products:hover {
    background: #ebebeb;
}

/* Card Footer */
.order-card-footer {
    padding: 16px 20px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
}

.order-info {
    display: flex;
    align-items: center;
}

.total-amount {
    display: flex;
    align-items: center;
}

.amount-value {
    font-size: 18px;
    font-weight: 600;
    color: #ee4d2d;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.action-btn {
    text-transform: none;
    font-size: 13px;
    font-weight: 500;
}

.action-btn :deep(.v-btn__content) {
    display: flex;
    align-items: center;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon {
    font-size: 80px;
    color: #d9d9d9;
}

.empty-text {
    font-size: 16px;
    color: #999;
    margin-top: 16px;
}

/* Pagination */
.custom-pagination :deep(.v-pagination__item--is-active) {
    background-color: #ee4d2d !important;
    color: white;
}

.custom-pagination :deep(.v-pagination__item) {
    min-width: 32px;
    height: 32px;
}

/* Responsive */
@media (max-width: 960px) {
    .order-tabs :deep(.v-tab) {
        min-width: auto;
        padding: 12px 16px;
        font-size: 13px;
    }

    .order-card-header,
    .shop-section,
    .order-card-footer {
        padding: 12px 16px;
    }

    .product-image {
        width: 60px;
        height: 60px;
    }

    .action-buttons {
        flex-wrap: wrap;
    }

    .action-btn {
        flex: 1;
        min-width: calc(50% - 6px);
    }
}

@media (max-width: 600px) {
    .order-tabs-wrapper {
        top: 56px;
    }

    .order-tabs :deep(.v-tab) {
        padding: 10px 12px;
        font-size: 12px;
    }

    .tab-count {
        display: none;
    }

    .shop-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .product-item {
        padding: 8px 0;
    }

    .product-name {
        font-size: 13px;
    }

    .product-price {
        font-size: 14px;
    }

    .amount-value {
        font-size: 16px;
    }
}

/* Gap utility */
.gap-2 {
    gap: 8px;
}

.gap-3 {
    gap: 12px;
}
</style>
