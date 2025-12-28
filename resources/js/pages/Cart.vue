<template>
    <v-container class="py-6">
        <v-row>
            <v-col cols="12">
                <h1 class="fs-24 fw-700 mb-4">{{ $t("cart") || "Giỏ Hàng" }}</h1>
                
                <!-- Cart Table Header -->
                <div v-if="hasCartItems" class="bg-white border rounded mb-4">
                    <div class="d-flex align-center pa-3 border-bottom bg-grey-lighten-4">
                        <v-checkbox
                            :model-value="allSelected"
                            @update:modelValue="toggleAllItems"
                            hide-details
                            class="mt-0"
                            color="primary"
                            true-icon="las la-check"
                        ></v-checkbox>
                        <div class="flex-shrink-0 ms-3 me-3" style="width: 80px;"></div>
                        <div class="flex-grow-1 minw-0 me-3 fw-600">{{ $t("product") || "Sản Phẩm" }}</div>
                        <div class="text-end me-6 fw-600" style="min-width: 120px;">{{ $t("unit_price") || "Đơn Giá" }}</div>
                        <div class="d-flex align-center justify-center me-6 fw-600" style="min-width: 120px;">{{ $t("quantity") || "Số Lượng" }}</div>
                        <div class="text-end me-6 fw-600" style="min-width: 120px;">{{ $t("amount") || "Số Tiền" }}</div>
                        <div class="fw-600" style="min-width: 60px;">{{ $t("actions") || "Thao Tác" }}</div>
                    </div>

                    <!-- Multi Vendor: Group by Shop -->
                    <template v-if="isMultiVendor">
                        <div
                            v-for="(shop, shopIndex) in getCartShops"
                            :key="shop.id"
                            class="border-bottom"
                        >
                            <!-- Shop Header -->
                            <div class="d-flex align-center pa-3 bg-grey-lighten-5">
                                <v-checkbox
                                    :model-value="shop.selected"
                                    @update:modelValue="toggleCartShop({ shop_id: shop.id, status: $event })"
                                    hide-details
                                    class="mt-0"
                                    color="primary"
                                    true-icon="las la-check"
                                ></v-checkbox>
                                <img
                                    :src="shop.logo"
                                    :alt="shop.name"
                                    class="size-32px ms-3 me-2 rounded"
                                />
                                <span class="fw-600">{{ shop.name }}</span>
                                <v-chip
                                    v-if="shop.couponCode"
                                    color="red"
                                    size="x-small"
                                    class="ms-2 white-text"
                                >
                                    VOUCHER
                                </v-chip>
                            </div>

                            <!-- Min Order Progress -->
                            <div v-if="getShopMinOrder(shop.id) > 0" class="px-3 py-2">
                                <min-order-progress
                                    :shop-id="shop.id"
                                    :cart-price="getShopCartPrice(shop.id)"
                                    :min-order="getShopMinOrder(shop.id)"
                                />
                            </div>

                            <!-- Shop Products -->
                            <div
                                v-for="(cartItem, itemIndex) in getShopProductsById(shop.id)"
                                :key="cartItem.cart_id"
                                class="d-flex align-center pa-3 border-top"
                                :class="{ 'opacity-50': cartItem.outOfStock }"
                            >
                                <v-checkbox
                                    :model-value="cartItem.selected"
                                    :disabled="cartItem.outOfStock"
                                    @update:modelValue="toggleCartItem({ cart_id: cartItem.cart_id, status: $event })"
                                    hide-details
                                    class="mt-0"
                                    color="primary"
                                    true-icon="las la-check"
                                ></v-checkbox>
                                
                                <div class="flex-shrink-0 ms-3 me-3">
                                    <img
                                        :src="cartItem.thumbnail"
                                        :alt="cartItem.name"
                                        class="img-fluid size-80px rounded"
                                        @error="imageFallback($event)"
                                    />
                                </div>
                                
                                <div class="flex-grow-1 minw-0 me-3">
                                    <div class="fw-500 mb-1">{{ cartItem.name }}</div>
                                    <div v-if="cartItem.combinations.length > 0" class="fs-12 opacity-70 mb-2">
                                        <span
                                            v-for="(combination, j) in cartItem.combinations"
                                            :key="j"
                                            class="me-2"
                                        >
                                            <span class="opacity-70">{{ combination.attribute }}:</span>
                                            <span class="fw-500">{{ combination.value }}</span>
                                        </span>
                                    </div>
                                    <v-chip
                                        v-if="cartItem.outOfStock"
                                        color="red"
                                        size="x-small"
                                        class="white-text"
                                    >
                                        {{ $t("out_of_stock") }}
                                    </v-chip>
                                </div>
                                
                                <div class="text-end me-6" style="min-width: 120px;">
                                    <del
                                        v-if="cartItem.regular_price > cartItem.dicounted_price"
                                        class="d-block fs-12 opacity-50 mb-1"
                                    >
                                        {{ format_price(cartItem.regular_price) }}
                                    </del>
                                    <div class="fw-600 text-primary">
                                        {{ format_price(cartItem.dicounted_price) }}
                                    </div>
                                </div>
                                
                                <div class="d-flex align-center justify-center me-6" style="min-width: 120px;">
                                    <v-btn
                                        color="primary"
                                        size="small"
                                        variant="outlined"
                                        icon
                                        :disabled="cartItem.qty <= 1 || cartItem.outOfStock"
                                        @click="updateQuantity({ type: 'minus', cart_id: cartItem.cart_id })"
                                    >
                                        <i class="las la-minus"></i>
                                    </v-btn>
                                    <span class="mx-3 fw-600">{{ cartItem.qty }}</span>
                                    <v-btn
                                        color="primary"
                                        size="small"
                                        variant="outlined"
                                        icon
                                        :disabled="cartItem.outOfStock"
                                        @click="updateQuantity({ type: 'plus', cart_id: cartItem.cart_id })"
                                    >
                                        <i class="las la-plus"></i>
                                    </v-btn>
                                </div>
                                
                                <div class="text-end me-6" style="min-width: 120px;">
                                    <div class="fw-700 text-primary fs-16">
                                        {{ format_price(cartItem.dicounted_price * cartItem.qty) }}
                                    </div>
                                </div>
                                
                                <div>
                                    <v-btn
                                        icon
                                        variant="text"
                                        size="small"
                                        @click="removeFromCart(cartItem.cart_id)"
                                    >
                                        <i class="las la-trash fs-18 opacity-70"></i>
                                    </v-btn>
                                </div>
                            </div>

                            <!-- Shop Voucher -->
                            <div class="px-3 py-2 border-top bg-grey-lighten-5">
                                <coupon-form :shop-id="shop.id" />
                            </div>
                        </div>
                    </template>

                    <!-- Single Vendor: Simple List -->
                    <template v-else>
                        <div
                            v-for="(cartItem, itemIndex) in getCartProducts"
                            :key="cartItem.cart_id"
                            class="d-flex align-center pa-3 border-top"
                            :class="{ 'opacity-50': cartItem.outOfStock }"
                        >
                            <v-checkbox
                                :model-value="cartItem.selected"
                                :disabled="cartItem.outOfStock"
                                @update:modelValue="toggleCartItem({ cart_id: cartItem.cart_id, status: $event })"
                                hide-details
                                class="mt-0"
                                color="primary"
                            ></v-checkbox>
                            
                            <div class="flex-shrink-0 ms-3 me-3">
                                <img
                                    :src="cartItem.thumbnail"
                                    :alt="cartItem.name"
                                    class="img-fluid size-80px rounded"
                                    @error="imageFallback($event)"
                                />
                            </div>
                            
                            <div class="flex-grow-1 minw-0 me-3">
                                <div class="fw-500 mb-1">{{ cartItem.name }}</div>
                                <div v-if="cartItem.combinations.length > 0" class="fs-12 opacity-70 mb-2">
                                    <span
                                        v-for="(combination, j) in cartItem.combinations"
                                        :key="j"
                                        class="me-2"
                                    >
                                        <span class="opacity-70">{{ combination.attribute }}:</span>
                                        <span class="fw-500">{{ combination.value }}</span>
                                    </span>
                                </div>
                                <v-chip
                                    v-if="cartItem.outOfStock"
                                    color="red"
                                    size="x-small"
                                    class="white-text"
                                >
                                    {{ $t("out_of_stock") }}
                                </v-chip>
                            </div>
                            
                            <div class="text-end me-6" style="min-width: 120px;">
                                <del
                                    v-if="cartItem.regular_price > cartItem.dicounted_price"
                                    class="d-block fs-12 opacity-50 mb-1"
                                >
                                    {{ format_price(cartItem.regular_price) }}
                                </del>
                                <div class="fw-600 text-primary">
                                    {{ format_price(cartItem.dicounted_price) }}
                                </div>
                            </div>
                            
                            <div class="d-flex align-center justify-center me-6" style="min-width: 120px;">
                                <v-btn
                                    color="primary"
                                    size="small"
                                    variant="outlined"
                                    icon
                                    :disabled="cartItem.qty <= 1 || cartItem.outOfStock"
                                    @click="updateQuantity({ type: 'minus', cart_id: cartItem.cart_id })"
                                >
                                    <i class="las la-minus"></i>
                                </v-btn>
                                <span class="mx-3 fw-600">{{ cartItem.qty }}</span>
                                <v-btn
                                    color="primary"
                                    size="small"
                                    variant="outlined"
                                    icon
                                    :disabled="cartItem.outOfStock"
                                    @click="updateQuantity({ type: 'plus', cart_id: cartItem.cart_id })"
                                >
                                    <i class="las la-plus"></i>
                                </v-btn>
                            </div>
                            
                            <div class="text-end me-6" style="min-width: 120px;">
                                <div class="fw-700 text-primary fs-16">
                                    {{ format_price(cartItem.dicounted_price * cartItem.qty) }}
                                </div>
                            </div>
                            
                            <div>
                                <v-btn
                                    icon
                                    variant="text"
                                    size="small"
                                    @click="removeFromCart(cartItem.cart_id)"
                                >
                                    <i class="las la-trash fs-18 opacity-70"></i>
                                </v-btn>
                            </div>
                        </div>

                        <!-- Single Vendor Voucher -->
                        <div class="px-3 py-3 border-top bg-grey-lighten-5">
                            <coupon-form />
                        </div>
                    </template>
                </div>

                <!-- Empty Cart -->
                <div v-else class="text-center py-12">
                    <img
                        class="img-fluid mb-4"
                        :src="static_asset('/assets/img/no-cart-item.jpg')"
                        alt="Empty cart"
                    />
                    <div class="fs-20 fw-500 mb-4">
                        {{ $t("your_shopping_bag_is_empty_start_shopping") }}
                    </div>
                    <v-btn
                        color="primary"
                        large
                        @click="$router.push({ name: 'Home' })"
                    >
                        {{ $t("continue_shopping") || "Tiếp Tục Mua Sắm" }}
                    </v-btn>
                </div>

                <!-- Cart Summary & Checkout - Fixed Footer -->
                <div v-if="hasCartItems" class="cart-footer-fixed bg-white border-top pa-4">
                    <v-row>
                        <v-col cols="12" md="8">
                            <div class="d-flex align-center flex-wrap gap-2">
                                <v-checkbox
                                    :model-value="allSelected"
                                    @update:modelValue="toggleAllItems"
                                    hide-details
                                    class="mt-0"
                                    color="primary"
                                    true-icon="las la-check"
                                >
                                    <template v-slot:label>
                                        <span class="fw-600">
                                            {{ $t("select_all") || "Chọn Tất Cả" }}
                                            ({{ getCartCount }})
                                        </span>
                                    </template>
                                </v-checkbox>
                                <v-btn
                                    variant="text"
                                    size="small"
                                    @click="removeSelected"
                                >
                                    {{ $t("delete") || "Xóa" }}
                                </v-btn>
                                <v-btn
                                    variant="text"
                                    size="small"
                                    @click="saveToWishlist"
                                >
                                    {{ $t("save_to_wishlist") || "Lưu vào mục Đã thích" }}
                                </v-btn>
                            </div>
                        </v-col>
                        <v-col cols="12" md="4">
                            <div class="text-end">
                                <div class="mb-2">
                                    <span class="opacity-70 me-2">
                                        {{ $t("total") || "Tổng cộng" }}
                                        ({{ getSelectedCount }} {{ $t("product") || "sản phẩm" }}):
                                    </span>
                                </div>
                                <div class="fs-24 fw-700 text-primary mb-2">
                                    {{ format_price(getCartPrice - getTotalCouponDiscount) }}
                                </div>
                                <div v-if="getTotalCouponDiscount > 0" class="fs-14 text-success mb-3">
                                    {{ $t("saved") || "Tiết kiệm" }}
                                    {{ format_price(getTotalCouponDiscount) }}
                                </div>
                                <v-btn
                                    color="primary"
                                    size="large"
                                    block
                                    elevation="0"
                                    @click="goToCheckout"
                                    :disabled="getCartPrice <= 0"
                                >
                                    {{ $t("buy_now") || "Mua Hàng" }}
                                </v-btn>
                            </div>
                        </v-col>
                    </v-row>
                </div>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from "vuex";
import CartItems from "../components/cart/CartItems.vue";
import CouponForm from "../components/cart/CouponForm.vue";
import MinOrderProgress from "../components/cart/MinOrderProgress.vue";
import Mixin from "../utils/mixin";

export default {
    name: "Cart",
    mixins: [Mixin],
    components: {
        CartItems,
        CouponForm,
        MinOrderProgress,
    },
    computed: {
        ...mapGetters("app", ["generalSettings"]),
        ...mapGetters("cart", [
            "getCartCount",
            "getCartPrice",
            "getCartProducts",
            "getCartShops",
            "getShopMinOrder",
            "getShopCartPrice",
            "getShopProductsById",
            "getTotalCouponDiscount",
            "getSelectedCartIds",
        ]),
        ...mapGetters("auth", ["isAuthenticated"]),
        isMultiVendor() {
            return this.is_addon_activated("multi_vendor");
        },
        hasCartItems() {
            return this.isMultiVendor
                ? this.getCartShops.length > 0
                : this.getCartProducts.length > 0;
        },
        allSelected() {
            if (this.isMultiVendor) {
                return (
                    this.getCartShops.length > 0 &&
                    this.getCartShops.every((shop) => shop.selected)
                );
            } else {
                const selectedItems = this.getCartProducts.filter(
                    (item) => item.selected && !item.outOfStock
                );
                const availableItems = this.getCartProducts.filter(
                    (item) => !item.outOfStock
                );
                return (
                    availableItems.length > 0 &&
                    selectedItems.length === availableItems.length
                );
            }
        },
        getSelectedCount() {
            if (this.isMultiVendor) {
                return this.getCartShops.reduce((count, shop) => {
                    if (shop.selected) {
                        return (
                            count +
                            this.getShopProductsById(shop.id).filter(
                                (item) => item.selected
                            ).length
                        );
                    }
                    return count;
                }, 0);
            } else {
                return this.getCartProducts.filter((item) => item.selected).length;
            }
        },
    },
    methods: {
        ...mapActions("cart", [
            "fetchCartProducts",
            "updateQuantity",
            "toggleCartItem",
            "toggleCartShop",
            "removeFromCart",
        ]),
        ...mapMutations("auth", ["showLoginDialog"]),
        toggleAllItems(status) {
            if (this.isMultiVendor) {
                this.getCartShops.forEach((shop) => {
                    this.toggleCartShop({ shop_id: shop.id, status });
                });
            } else {
                this.getCartProducts.forEach((item) => {
                    if (!item.outOfStock) {
                        this.toggleCartItem({
                            cart_id: item.cart_id,
                            status,
                        });
                    }
                });
            }
        },
        removeSelected() {
            const selectedIds = this.getSelectedCartIds;
            if (selectedIds.length === 0) {
                this.snack({
                    message: this.$t("please_select_items_to_delete"),
                    color: "red",
                });
                return;
            }
            selectedIds.forEach((id) => {
                this.removeFromCart(id);
            });
            this.snack({
                message: this.$t("selected_items_removed"),
            });
        },
        saveToWishlist() {
            // TODO: Implement save to wishlist
            this.snack({
                message: this.$t("feature_coming_soon") || "Tính năng đang phát triển",
            });
        },
        goToCheckout() {
            if (this.getCartPrice <= 0) {
                this.snack({
                    message: this.$t("please_select_a_cart_product"),
                    color: "red",
                });
                return;
            }
            if (
                this.isAuthenticated ||
                !this.generalSettings.guest_checkout_activation
            ) {
                this.$router.push({ name: "Checkout" });
            } else {
                this.$router.push({ name: "GuestCheckout" });
            }
        },
    },
    created() {
        this.fetchCartProducts();
    },
};
</script>

<style scoped>
.size-32px {
    width: 32px;
    height: 32px;
    object-fit: cover;
}

.size-80px {
    width: 80px;
    height: 80px;
    object-fit: cover;
}

.minw-0 {
    min-width: 0;
}

.gap-2 {
    gap: 0.5rem;
}

.cart-footer-fixed {
    position: sticky;
    bottom: 0;
    z-index: 100;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
}
</style>

