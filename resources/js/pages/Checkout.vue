<template>
    <v-container class="pt-7 checkout-shopee-style">
        <v-row>
            <v-col xl="10" lg="11" class="mx-auto">
                <!-- Delivery Type Selection (Hidden but functional) -->
                <div class="delivery-type-selection mb-3" v-show="false">
                    <v-row>
                        <v-col cols="12" sm="6">
                            <label>
                                <input
                                    type="radio"
                                    name="delivery_type"
                                    v-model="selectedDeliveryType"
                                    value="home_delivery"
                                    @click="ChooseDeleviryType('home_delivery')"
                                />
                                {{ $t("home_delivery") }}
                            </label>
                        </v-col>
                        <v-col
                            cols="12"
                            sm="6"
                            v-if="getIsPickup && generalSettings.pickup_point"
                        >
                            <label>
                                <input
                                    type="radio"
                                    name="delivery_type"
                                    v-model="selectedDeliveryType"
                                    value="pickup"
                                    @click="checkForPickUp('pickup')"
                                />
                                {{ $t("pickup") }}
                            </label>
                        </v-col>
                    </v-row>
                    <!-- Pickup Point Selection -->
                    <div
                        v-if="
                            generalSettings.pickup_point &&
                            selectedDeliveryType == 'pickup' &&
                            for_pickup
                        "
                    >
                        <v-autocomplete
                            v-model="selectedPickupPoint"
                            :items="getPickupPoints"
                            :label="$t('select_pickup_point')"
                            hide-details="auto"
                            variant="outlined"
                            item-title="name"
                            item-value="id"
                            dense
                            autocomplete="off"
                            class="mb-3"
                        ></v-autocomplete>
                    </div>
                </div>

                <!-- Address Dialog -->
                <address-dialog
                    :show="addDialogShow"
                    @close="addressDialogClosed"
                    :old-address="addressSelectedForEdit"
                />

                <!-- Shipping Address Section - Shopee Style -->
                <div
                    class="address-section bg-white rounded pa-4 mb-3"
                    v-if="selectedDeliveryType == 'home_delivery'"
                >
                    <div class="d-flex align-center mb-3">
                        <i
                            class="las la-map-marker-alt primary--text fs-20 me-2"
                        ></i>
                        <h3 class="fs-16 fw-600 mb-0 primary--text">
                            {{ $t("shipping_address") }}
                        </h3>
                    </div>

                    <template v-if="getAddresses.length > 0">
                        <div
                            v-for="address in getAddresses"
                            :key="'shipping-' + address.id"
                            v-show="selectedShippingAddressId == address.id"
                            class="selected-address"
                        >
                            <div class="d-flex align-start">
                                <div class="flex-grow-1">
                                    <div class="fw-600 mb-1">
                                        {{ address.first_name }}
                                        {{ address.last_name }}
                                        <span class="ms-2 opacity-80"
                                            >(+{{ address.phone }})</span
                                        >
                                    </div>
                                    <div class="opacity-80 fs-14">
                                        {{ address.address }},
                                        {{ address.city }}, {{ address.state }},
                                        {{ address.country }} -
                                        {{ address.postal_code }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2 ms-3">
                                    <v-chip
                                        v-if="address.default_shipping"
                                        small
                                        outlined
                                        color="primary"
                                        class="px-3"
                                    >
                                        {{ $t("default") || "Mặc Định" }}
                                    </v-chip>
                                    <v-btn
                                        small
                                        text
                                        color="primary"
                                        @click="showAddressSelection = true"
                                    >
                                        {{ $t("change") }}
                                    </v-btn>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <v-btn
                            color="primary"
                            outlined
                            @click.stop="addDialogShow = true"
                        >
                            <i class="las la-plus me-2"></i>
                            {{ $t("add_new_address") }}
                        </v-btn>
                    </template>

                    <!-- Address Selection Dialog -->
                    <v-dialog v-model="showAddressSelection" max-width="700">
                        <v-card>
                            <v-card-title
                                class="d-flex justify-space-between align-center"
                            >
                                <span>{{ $t("select_address") }}</span>
                                <v-btn
                                    icon
                                    @click="showAddressSelection = false"
                                >
                                    <i class="las la-times"></i>
                                </v-btn>
                            </v-card-title>
                            <v-divider></v-divider>
                            <v-card-text
                                class="pa-4"
                                style="max-height: 500px; overflow-y: auto"
                            >
                                <div
                                    v-for="address in getAddresses"
                                    :key="'select-shipping-' + address.id"
                                    class="address-option pa-3 mb-2 rounded border"
                                    :class="{
                                        'border-primary':
                                            selectedShippingAddressId ==
                                            address.id,
                                    }"
                                    @click="selectShippingAddress(address.id)"
                                    style="cursor: pointer"
                                >
                                    <div class="d-flex align-start">
                                        <v-radio
                                            :value="address.id"
                                            :model-value="
                                                selectedShippingAddressId
                                            "
                                            color="primary"
                                            class="mt-0 me-3"
                                        ></v-radio>
                                        <div class="flex-grow-1">
                                            <div class="fw-600 mb-1">
                                                {{ address.first_name }}
                                                {{ address.last_name }}
                                                <span class="ms-2"
                                                    >(+{{
                                                        address.phone
                                                    }})</span
                                                >
                                                <v-chip
                                                    v-if="
                                                        address.default_shipping
                                                    "
                                                    x-small
                                                    color="primary"
                                                    class="ms-2"
                                                >
                                                    {{ $t("default") }}
                                                </v-chip>
                                            </div>
                                            <div class="opacity-80 fs-14">
                                                {{ address.address }},
                                                {{ address.city }},
                                                {{ address.state }},
                                                {{ address.country }} -
                                                {{ address.postal_code }}
                                            </div>
                                        </div>
                                        <v-btn
                                            icon
                                            small
                                            @click.stop="editAddress(address)"
                                        >
                                            <i class="las la-edit"></i>
                                        </v-btn>
                                    </div>
                                </div>
                                <v-btn
                                    class="mt-3"
                                    color="primary"
                                    outlined
                                    block
                                    @click.stop="
                                        addDialogShow = true;
                                        showAddressSelection = false;
                                    "
                                >
                                    <i class="las la-plus me-2"></i>
                                    {{ $t("add_new_address") }}
                                </v-btn>
                            </v-card-text>
                            <v-divider></v-divider>
                            <v-card-actions>
                                <v-spacer></v-spacer>
                                <v-btn
                                    color="primary"
                                    @click="showAddressSelection = false"
                                >
                                    {{ $t("confirm") || "Xác nhận" }}
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </div>

                <!-- Billing Address (Hidden Selection) -->
                <div
                    v-if="selectedDeliveryType == 'home_delivery'"
                    v-show="false"
                >
                    <div
                        v-for="address in getAddresses"
                        :key="'billing-' + address.id"
                    >
                        <label>
                            <input
                                type="radio"
                                name="checkout_billing"
                                v-model="selectedBillingAddressId"
                                :value="address.id"
                                :checked="address.default_billing"
                            />
                        </label>
                    </div>
                </div>

                <!-- Products Section - Shopee Style Table -->
                <div class="products-section bg-white rounded pa-4 mb-3">
                    <div
                        class="products-header d-none d-sm-flex pa-3 grey lighten-4 rounded mb-2"
                    >
                        <div class="flex-grow-1" style="flex: 1">
                            {{ $t("products") || "Sản phẩm" }}
                        </div>
                        <div class="text-center" style="width: 120px">
                            {{ $t("unit_price") || "Đơn giá" }}
                        </div>
                        <div class="text-center" style="width: 100px">
                            {{ $t("quantity") || "Số lượng" }}
                        </div>
                        <div class="text-end" style="width: 120px">
                            {{ $t("amount") || "Thành tiền" }}
                        </div>
                    </div>

                    <div
                        v-for="product in getCartProducts"
                        :key="product.id"
                        class="product-item d-flex align-start pa-3 mb-2 border-bottom"
                    >
                        <div
                            class="d-flex align-start flex-grow-1"
                            style="flex: 1"
                        >
                            <img
                                :src="product.thumbnail || product.product_thumbnail_image || static_asset('/assets/img/placeholder.jpg')"
                                class="rounded me-3"
                                @error="imageFallback($event)"
                                style="
                                    width: 80px;
                                    height: 80px;
                                    object-fit: cover;
                                "
                            />
                            <div>
                                <div class="fw-500 mb-1">
                                    {{ product.name || product.product_name }}
                                </div>
                                <div
                                    class="fs-12 grey--text"
                                    v-if="product.combinations && product.combinations.length > 0"
                                >
                                    {{ $t("variant") }}: {{ formatVariation(product.combinations) }}
                                </div>
                            </div>
                        </div>
                        <div
                            class="text-center d-none d-sm-block"
                            style="width: 120px"
                        >
                            <div class="opacity-80">
                                {{ format_price(getProductPrice(product)) }}
                            </div>
                        </div>
                        <div
                            class="text-center d-none d-sm-block"
                            style="width: 100px"
                        >
                            <div>{{ product.qty || product.quantity || 0 }}</div>
                        </div>
                        <div
                            class="text-end d-none d-sm-block"
                            style="width: 120px"
                        >
                            <div class="fw-600 primary--text">
                                {{
                                    format_price(
                                        getProductPrice(product) * (product.qty || product.quantity || 0)
                                    )
                                }}
                            </div>
                        </div>

                        <!-- Mobile View -->
                        <div
                            class="d-flex d-sm-none flex-column align-end ms-auto"
                        >
                            <div class="fw-600 primary--text mb-1">
                                {{
                                    format_price(
                                        getProductPrice(product) * (product.qty || product.quantity || 0)
                                    )
                                }}
                            </div>
                            <div class="fs-12 opacity-80">
                                x{{ product.qty || product.quantity || 0 }}
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Section -->
                    <div
                        class="voucher-section pa-3 mb-2"
                        v-if="!is_addon_activated('multi_vendor')"
                    >
                        <div class="d-flex align-center">
                            <i
                                class="las la-ticket-alt primary--text fs-20 me-2"
                            ></i>
                            <span class="flex-grow-1">{{
                                $t("shop_voucher") || "Voucher của Shop"
                            }}</span>
                            <coupon-form for-checkout />
                        </div>
                    </div>

                    <!-- Delivery Option Section -->
                    <div
                        class="delivery-option-section pa-3"
                        v-if="selectedDeliveryType == 'home_delivery'"
                    >
                        <div
                            v-if="selectedDeliveryOption !== ''"
                            class="delivery-options"
                        >
                            <div class="d-flex flex-wrap gap-2">
                                <label
                                    class="delivery-option-item pa-3 rounded border cursor-pointer d-flex align-center flex-grow-1"
                                    :class="{
                                        'border-primary bg-soft-primary':
                                            selectedDeliveryOption ===
                                            'standard',
                                    }"
                                >
                                    <input
                                        type="radio"
                                        name="delivery_option"
                                        v-model="selectedDeliveryOption"
                                        value="standard"
                                        class="me-2"
                                    />
                                    <div class="flex-grow-1">
                                        <div class="fw-600">
                                            {{
                                                $t("standard_delivery") ||
                                                "Tiêu chuẩn"
                                            }}
                                        </div>
                                        <div class="fs-13 opacity-80">
                                            {{ getStandardTime }}
                                            {{ $t("days") }} -
                                            {{
                                                format_price(
                                                    standardDeliveryCost
                                                )
                                            }}
                                        </div>
                                    </div>
                                </label>

                                <label
                                    v-if="
                                        generalSettings.express_delivery_option
                                    "
                                    class="delivery-option-item pa-3 rounded border cursor-pointer d-flex align-center flex-grow-1"
                                    :class="{
                                        'border-primary bg-soft-primary':
                                            selectedDeliveryOption ===
                                            'express',
                                    }"
                                >
                                    <input
                                        type="radio"
                                        name="delivery_option"
                                        v-model="selectedDeliveryOption"
                                        value="express"
                                        class="me-2"
                                    />
                                    <div class="flex-grow-1">
                                        <div class="fw-600">
                                            {{
                                                $t("express_delivery") ||
                                                "Nhanh"
                                            }}
                                        </div>
                                        <div class="fs-13 opacity-80">
                                            {{ getExpressTime }}
                                            {{ $t("days") }} -
                                            {{
                                                format_price(
                                                    expressDeliveryCost
                                                )
                                            }}
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Message Section -->
                    <div class="message-section pa-3 border-top">
                        <div class="d-flex align-center">
                            <span
                                class="opacity-80 me-3"
                                style="min-width: 80px"
                                >{{ $t("note") || "Lời nhắn" }}:</span
                            >
                            <v-text-field
                                v-model="orderNote"
                                :placeholder="
                                    $t('note_to_seller') ||
                                    'Lưu ý cho Người bán...'
                                "
                                dense
                                outlined
                                hide-details
                                class="flex-grow-1"
                            ></v-text-field>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Section - Compact -->
                <div class="order-summary-section bg-white rounded pa-4 mb-3">
                    <div
                        class="d-flex justify-space-between align-center pa-2"
                        v-if="generalSettings.club_point == 1"
                    >
                        <span class="opacity-80">{{
                            $t("total_club_points")
                        }}</span>
                        <span class="fw-600 primary--text">{{
                            getCartClubPoints
                        }}</span>
                    </div>

                    <div class="summary-rows">
                        <div
                            class="d-flex justify-space-between align-center pa-2"
                        >
                            <span class="opacity-80">{{
                                $t("sub_total")
                            }}</span>
                            <span>{{
                                format_price(getCartPrice - getCartTax, false)
                            }}</span>
                        </div>

                        <div
                            class="d-flex justify-space-between align-center pa-2"
                        >
                            <span class="opacity-80">{{
                                $t("shipping_charge")
                            }}</span>
                            <span>
                                {{
                                    selectedDeliveryType == "home_delivery"
                                        ? selectedDeliveryOption === "standard"
                                            ? format_price(
                                                  standardDeliveryCost *
                                                      getCartShops.length
                                              )
                                            : format_price(
                                                  expressDeliveryCost *
                                                      getCartShops.length
                                              )
                                        : format_price(0)
                                }}
                            </span>
                        </div>

                        <div
                            class="d-flex justify-space-between align-center pa-2"
                        >
                            <span class="opacity-80">{{ $t("tax") }}</span>
                            <span>{{ format_price(getCartTax, false) }}</span>
                        </div>

                        <div
                            class="d-flex justify-space-between align-center pa-2"
                            v-if="getTotalCouponDiscount > 0"
                        >
                            <span class="opacity-80">{{ $t("discount") }}</span>
                            <span class="error--text"
                                >-{{
                                    format_price(getTotalCouponDiscount)
                                }}</span
                            >
                        </div>

                        <v-divider class="my-2"></v-divider>

                        <div
                            class="d-flex justify-space-between align-center pa-2"
                        >
                            <span class="fs-16 fw-600">{{
                                $t("total_to_pay")
                            }}</span>
                            <span class="fs-20 fw-700 primary--text">{{
                                format_price(totalPrice, false)
                            }}</span>
                        </div>
                    </div>
                </div>
                <!-- Payment Section - Shopee Style -->
                <div class="payment-section bg-white rounded pa-4 mb-3">
                    <div class="d-flex align-center mb-3">
                        <i
                            class="las la-credit-card primary--text fs-20 me-2"
                        ></i>
                        <h3 class="fs-16 fw-600 mb-0">
                            {{
                                $t("payment_options") ||
                                "Phương thức thanh toán"
                            }}
                        </h3>
                    </div>

                    <div class="payment-methods">
                        <v-row class="mb-3">
                            <!-- online payment methods -->
                            <v-col
                                cols="6"
                                sm="4"
                                md="3"
                                v-for="(paymentMethod, i) in paymentMethods"
                                :key="'online-' + i"
                                :class="[
                                    paymentMethod.status == 1 ? '' : 'd-none',
                                ]"
                            >
                                <label
                                    class="payment-method-card d-block cursor-pointer"
                                    v-if="
                                        getIsDigital &&
                                        paymentMethod.code != 'cash_on_delivery'
                                    "
                                >
                                    <input
                                        type="radio"
                                        name="checkout_payment_method"
                                        :checked="
                                            selectedPaymentMethod &&
                                            paymentMethod.code ==
                                                selectedPaymentMethod.code
                                        "
                                        @change="
                                            paymentSelected(
                                                $event,
                                                paymentMethod
                                            )
                                        "
                                        style="display: none"
                                    />
                                    <span
                                        class="d-block pa-3 text-center rounded border transition"
                                        :class="{
                                            'border-primary bg-soft-primary':
                                                selectedPaymentMethod &&
                                                paymentMethod.code ==
                                                    selectedPaymentMethod.code,
                                            'border-gray-300':
                                                !selectedPaymentMethod ||
                                                paymentMethod.code !=
                                                    selectedPaymentMethod.code,
                                        }"
                                    >
                                        <img
                                            :src="paymentMethod.img"
                                            class="img-fluid w-100 mb-2"
                                            style="
                                                height: 40px;
                                                object-fit: contain;
                                            "
                                        />
                                        <span class="fw-600 fs-13">{{
                                            paymentMethod.name
                                        }}</span>
                                    </span>
                                </label>
                                <label
                                    class="payment-method-card d-block cursor-pointer"
                                    v-else-if="!getIsDigital"
                                >
                                    <input
                                        type="radio"
                                        name="checkout_payment_method"
                                        :checked="
                                            selectedPaymentMethod &&
                                            paymentMethod.code ==
                                                selectedPaymentMethod.code
                                        "
                                        @change="
                                            paymentSelected(
                                                $event,
                                                paymentMethod
                                            )
                                        "
                                        style="display: none"
                                    />
                                    <span
                                        class="d-block pa-3 text-center rounded border transition"
                                        :class="{
                                            'border-primary bg-soft-primary':
                                                selectedPaymentMethod &&
                                                paymentMethod.code ==
                                                    selectedPaymentMethod.code,
                                            'border-gray-300':
                                                !selectedPaymentMethod ||
                                                paymentMethod.code !=
                                                    selectedPaymentMethod.code,
                                        }"
                                    >
                                        <img
                                            :src="paymentMethod.img"
                                            class="img-fluid w-100 mb-2"
                                            style="
                                                height: 40px;
                                                object-fit: contain;
                                            "
                                        />
                                        <span class="fw-600 fs-13">{{
                                            paymentMethod.name
                                        }}</span>
                                    </span>
                                </label>
                            </v-col>
                            <!-- online payment methods ends -->

                            <!-- offline payment methods -->
                            <v-col
                                cols="6"
                                sm="4"
                                md="3"
                                v-for="offlinePaymentMethod in offlinePaymentMethods"
                                :key="'offline-' + offlinePaymentMethod.code"
                            >
                                <label
                                    class="payment-method-card d-block cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        name="wallet_payment_method"
                                        :checked="
                                            selectedPaymentMethod &&
                                            offlinePaymentMethod.code ==
                                                selectedPaymentMethod.code
                                        "
                                        @change="
                                            paymentSelected(
                                                $event,
                                                offlinePaymentMethod
                                            )
                                        "
                                        style="display: none"
                                    />
                                    <span
                                        class="d-block pa-3 text-center rounded border transition"
                                        :class="{
                                            'border-primary bg-soft-primary':
                                                selectedPaymentMethod &&
                                                offlinePaymentMethod.code ==
                                                    selectedPaymentMethod.code,
                                            'border-gray-300':
                                                !selectedPaymentMethod ||
                                                offlinePaymentMethod.code !=
                                                    selectedPaymentMethod.code,
                                        }"
                                    >
                                        <img
                                            :src="offlinePaymentMethod.img"
                                            class="w-100 mb-2"
                                            style="
                                                height: 40px;
                                                object-fit: contain;
                                            "
                                        />
                                        <span class="fw-600 fs-13">{{
                                            offlinePaymentMethod.name
                                        }}</span>
                                    </span>
                                </label>
                            </v-col>
                            <!-- offline payment methods loop ends -->
                        </v-row>
                    </div>

                    <!-- show authorize net payment method's data -->
                    <div
                        v-if="
                            selectedPaymentMethod &&
                            selectedPaymentMethod.code == 'authorizenet'
                        "
                        class="my-3"
                    >
                        <h3 class="opacity-80 mb-3 fs-18 text-capitalize">
                            {{ $t("account_details") }}
                        </h3>
                        <div class="border px-2 py-2">
                            <!-- show authorize payment method's inputs -->
                            <v-text-field
                                variant="plain"
                                class="my-2 text-field"
                                :placeholder="
                                    $t('please_enter_valid_card_number')
                                "
                                type="text"
                                v-model="authorizeNet.card_number"
                                hide-details="auto"
                                required
                                outlined
                            >
                            </v-text-field>

                            <v-text-field
                                variant="plain"
                                class="my-2 text-field"
                                :placeholder="$t('please_enter_cvv')"
                                type="text"
                                v-model="authorizeNet.cvv"
                                hide-details="auto"
                                required
                                outlined
                            >
                            </v-text-field>

                            <v-autocomplete
                                variant="plain"
                                v-model="authorizeNet.expiration_month"
                                :items="months"
                                placeholder="$t('select_month')"
                                hide-details="auto"
                                class="mb-3 text-field"
                                outlined
                                allow-overflow
                                dense
                                required
                                :label="$t('select_month')"
                            ></v-autocomplete>
                            <v-autocomplete
                                variant="plain"
                                v-model="authorizeNet.expiration_year"
                                :items="dateLoop"
                                placeholder="$t('select_year')"
                                hide-details="auto"
                                class="mb-3 text-field"
                                outlined
                                allow-overflow
                                dense
                                required
                                :label="$t('select_year')"
                            ></v-autocomplete>
                            <!-- show authorize payment method's inputs -->
                        </div>
                    </div>

                    <!-- show offline payment method's data -->
                    <div
                        v-if="
                            selectedPaymentMethod &&
                            selectedPaymentMethod.code.includes(
                                'offline_payment'
                            )
                        "
                        class="my-3"
                    >
                        <h3 class="opacity-80 mb-3 fs-18 text-capitalize">
                            {{ $t("account_details") }}
                        </h3>
                        <div class="border px-2 py-2">
                            <div class="text-capitalize my-1">
                                <span class="font-weight-bold">{{
                                    $t("payment_name")
                                }}</span>
                                : {{ selectedPaymentMethod.name }}
                            </div>
                            <div class="text-capitalize my-1">
                                <span class="font-weight-bold">{{
                                    $t("payment_type")
                                }}</span>
                                : {{ selectedPaymentMethod.type_show }}
                            </div>
                            <div
                                class="text-capitalize d-flex my-1"
                                v-if="selectedPaymentMethod.description"
                            >
                                <span class="font-weight-bold me-1"
                                    >{{ $t("description") }} :</span
                                >
                                <span
                                    v-html="selectedPaymentMethod.description"
                                ></span>
                            </div>
                            <div
                                class="text-capitalize"
                                v-if="
                                    selectedPaymentMethod.bank_info.length > 0
                                "
                            >
                                <span class="font-weight-bold"
                                    >{{ $t("bank_info") }}:</span
                                >
                                <div
                                    class="border px-2 py-2 mt-2"
                                    v-for="bankInfo in selectedPaymentMethod.bank_info"
                                    :key="bankInfo.bank_name"
                                >
                                    <div>
                                        {{ $t("bank_name") }}:
                                        {{ bankInfo.bank_name }}
                                    </div>
                                    <div>
                                        {{ $t("account_name") }}:
                                        {{ bankInfo.account_name }}
                                    </div>
                                    <div>
                                        {{ $t("account_number") }}:
                                        {{ bankInfo.account_number }}
                                    </div>
                                    <div>
                                        {{ $t("routing_number") }}:
                                        {{ bankInfo.routing_number }}
                                    </div>
                                </div>
                            </div>

                            <!-- show offline payment method's inputs -->
                            <div
                                v-if="
                                    selectedPaymentMethod &&
                                    selectedPaymentMethod.code.includes(
                                        'offline_payment'
                                    )
                                "
                            >
                                <v-text-field
                                    class="my-2 text-field"
                                    :placeholder="$t('transaction_id')"
                                    type="text"
                                    v-model="transactionId"
                                    hide-details="auto"
                                    required
                                    variant="plain"
                                >
                                </v-text-field>
                                <v-file-input
                                    accept="image/*"
                                    :label="$t('add_receipt')"
                                    :placeholder="$t('add_receipt')"
                                    flat
                                    variant="plain"
                                    class="text-field"
                                    prepend-icon=""
                                    clearable
                                    v-model="receipt"
                                ></v-file-input>
                            </div>
                            <!-- show offline payment method's inputs -->
                        </div>
                    </div>

                    <!-- Wallet Payment Option -->
                    <template v-if="generalSettings.wallet_system == 1">
                        <v-divider class="my-3"></v-divider>
                        <div class="fs-14 opacity-80 mb-2 text-center">
                            {{ $t("or") }}
                        </div>
                        <div
                            class="wallet-section border rounded pa-4 cursor-pointer transition"
                            :class="{
                                'bg-soft-primary border-primary':
                                    selectedPaymentMethod &&
                                    selectedPaymentMethod.code == 'wallet',
                                'border-gray-300':
                                    !selectedPaymentMethod ||
                                    selectedPaymentMethod.code != 'wallet',
                            }"
                            @click="walletSelected()"
                        >
                            <recharge-dialog
                                :show="rechargeDialogShow"
                                from="/checkout"
                                @close="rechargeDialogClosed"
                            />
                            <div
                                class="d-flex flex-column flex-sm-row align-center justify-space-between"
                            >
                                <div class="mb-3 mb-sm-0">
                                    <div class="fw-600 mb-2">
                                        <i class="las la-wallet me-1"></i>
                                        {{ $t("pay_with_wallet") }}
                                    </div>
                                    <div class="fs-14">
                                        <span class="opacity-80"
                                            >{{ $t("your_wallet_balance") }}:
                                        </span>
                                        <span class="fw-600 primary--text">{{
                                            format_price(currentUser.balance)
                                        }}</span>
                                    </div>
                                    <div
                                        v-if="
                                            selectedPaymentMethod &&
                                            selectedPaymentMethod.code ==
                                                'wallet'
                                        "
                                        class="fs-13 mt-1"
                                    >
                                        <span class="opacity-80"
                                            >{{ $t("remaining_balance") }}:
                                        </span>
                                        <span class="fw-600">{{
                                            format_price(
                                                currentUser.balance - totalPrice
                                            )
                                        }}</span>
                                    </div>
                                </div>
                                <v-btn
                                    color="primary"
                                    outlined
                                    small
                                    @click.stop="rechargeDialogShow = true"
                                >
                                    {{ $t("recharge_wallet") }}
                                </v-btn>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Terms and Conditions -->
                <div class="terms-section bg-white rounded pa-4 mb-3">
                    <div class="d-flex align-start">
                        <input
                            class="me-2 mt-1"
                            type="checkbox"
                            id="checkbox"
                            v-model="checkbox"
                            style="width: 16px; height: 16px; cursor: pointer"
                        />
                        <label
                            for="checkbox"
                            class="fs-14 opacity-80"
                            style="cursor: pointer"
                        >
                            {{ $t("by_clicking_proceed_i_agree_to") }}
                            {{ $store.getters["app/appName"] }}'s
                            <router-link
                                :to="{
                                    name: 'CustomPage',
                                    params: {
                                        pageSlug: 'terms-and-conditions',
                                    },
                                }"
                                class="primary--text fw-500"
                            >
                                {{ $t("terms_and_conditions") }} </router-link
                            >,
                            <router-link
                                :to="{
                                    name: 'CustomPage',
                                    params: { pageSlug: 'return-policy' },
                                }"
                                class="primary--text fw-500"
                            >
                                {{ $t("return_policy") }}
                            </router-link>
                            {{ $t("and") }}
                            <router-link
                                :to="{
                                    name: 'CustomPage',
                                    params: { pageSlug: 'privacy-policy' },
                                }"
                                class="primary--text fw-500"
                            >
                                {{ $t("privacy_policy") }}
                            </router-link>
                        </label>
                    </div>
                </div>

                <!-- Place Order Button - Shopee Style -->
                <div
                    class="checkout-footer bg-white rounded pa-4 sticky-bottom"
                >
                    <div
                        class="d-flex flex-column flex-sm-row align-center justify-space-between"
                    >
                        <div class="mb-3 mb-sm-0 text-center text-sm-left">
                            <div class="fs-14 opacity-80">
                                {{ $t("total") || "Tổng thanh toán" }}
                                <span class="fw-600"
                                    >({{ getSelectedCartIds.length }}
                                    {{ $t("products") || "sản phẩm" }})</span
                                >:
                            </div>
                            <div class="fs-24 fw-700 primary--text">
                                {{ format_price(totalPrice) }}
                            </div>
                        </div>
                        <v-btn
                            elevation="0"
                            color="primary"
                            x-large
                            class="px-10"
                            @click="proceedCheckout"
                            :loading="checkoutLoading"
                            :disabled="checkoutLoading || !checkbox"
                        >
                            <span class="white--text fs-16 fw-600">
                                {{ $t("place_order") || "Đặt hàng" }}
                            </span>
                        </v-btn>
                    </div>
                </div>
                <Payment ref="makePayment" />
                <FailedDialog ref="failedPayment" />
                <v-overlay :value="checkoutLoading" z-index="99999">
                    <v-progress-circular
                        indeterminate
                        size="64"
                    ></v-progress-circular>
                </v-overlay>
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import AddressDialog from "../components/address/AddressDialog.vue";
import CouponForm from "../components/cart/CouponForm.vue";
import RechargeDialog from "../components/wallet/RechargeDialog.vue";
import FailedDialog from "./../components/payment/FailedDialog.vue";
import Payment from "./../components/payment/Payment.vue";
export default {
    head: {
        title: "Checkout Page",
    },
    name: "AizShopCheckout",
    components: {},
    data() {
        return {
            for_pickup: true,
            selectedPickupPoint: null,
            checkbox: false,
            checkoutLoading: false,
            selectedShippingAddressId: null,
            selectedBillingAddressId: null,
            selectedPaymentMethod: null,
            selectedDeliveryOption: "standard",
            selectedDeliveryType: "home_delivery",
            standardDeliveryCost: 0,
            expressDeliveryCost: 0,
            addDialogShow: false,
            showAddressSelection: false,
            addressSelectedForEdit: {},
            rechargeDialogShow: false,
            transactionId: null,
            receipt: null,
            orderNote: "",
            authorizeNet: {
                card_number: "",
                cvv: "",
                expiration_month: "",
                expiration_year: "",
            },
            address: [],
            months: [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec",
            ],
            dateloop: [],
        };
    },
    components: {
        AddressDialog,
        RechargeDialog,
        Payment,
        FailedDialog,
        CouponForm,
    },
    computed: {
        ...mapGetters("app", [
            "generalSettings",
            "appUrl",
            "paymentMethods",
            "offlinePaymentMethods",
            "demoMode",
        ]),
        ...mapGetters("address", [
            "getAddresses",
            "getDefaultShippingAddress",
            "getDefaultBillingAddress",
        ]),
        ...mapGetters("cart", [
            "getCartPrice",
            "getTotalCouponDiscount",
            "getCartClubPoints",
            "getCartTax",
            "getCartShops",
            "getStandardTime",
            "getExpressTime",
            "getAllCouponCodes",
            "getSelectedCartIds",
            "checkShopMinOrder",
            "getIsDigital",
            "getPickupPoints",
            "getCartProducts",
            "getIsPickup",
        ]),
        ...mapGetters("auth", ["currentUser"]),
        totalPrice() {
            return this.selectedDeliveryType == "home_delivery"
                ? this.selectedDeliveryOption === "standard"
                    ? this.getCartPrice -
                      this.getTotalCouponDiscount +
                      this.standardDeliveryCost * this.getCartShops.length
                    : this.getCartPrice -
                      this.getTotalCouponDiscount +
                      this.expressDeliveryCost * this.getCartShops.length
                : this.getCartPrice - this.getTotalCouponDiscount;
        },
    },
    methods: {
        getProductPrice(product) {
            // Ưu tiên dicounted_price (typo từ API), sau đó regular_price, cuối cùng là 0
            const price = product.dicounted_price || product.discounted_price || product.regular_price || product.price || 0;
            return parseFloat(price) || 0;
        },
        formatVariation(combinations) {
            if (!combinations || !Array.isArray(combinations)) return '';
            return combinations.map(c => {
                if (typeof c === 'string') return c;
                return c.attribute_value_name || c.value || '';
            }).filter(Boolean).join(', ');
        },
        imageFallback(event) {
            const placeholder = this.static_asset('/assets/img/placeholder.jpg');
            if (event.target.src !== placeholder) {
                event.target.src = placeholder;
            }
        },
        ...mapActions("cart", [
            "resetCoupon",
            "removeMultipleFromCart",
            "fetchCartProducts",
            "fetchPickupPoints",
        ]),
        ...mapActions("address", ["fetchAddresses"]),
        ...mapActions("auth", ["rechargeWallet", "deductFromWallet"]),

        // check for pick up availability
        async checkForPickUp(type) {
            this.getCartProducts.map((product) => {
                if (product.for_pickup == 0) {
                    this.selectedPickupPoint = null;
                    this.for_pickup = false;
                    this.snack({
                        message: `One or more items in the cart are not available for pickup`,
                        color: "red",
                    });
                    return;
                } else {
                    this.for_pickup = true;
                }
            });
            this.selectedDeliveryType = type;
        },

        ChooseDeleviryType(deliveryType) {
            this.selectedDeliveryType = deliveryType;
        },

        addressDialogClosed() {
            this.addressSelectedForEdit = {};
            this.addDialogShow = false;

            if (this.getAddresses.length == 1) {
                this.selectedShippingAddressId = this.getAddresses[0].id;
                this.selectedBillingAddressId = this.getAddresses[0].id;
                this.getShippingCost(this.getAddresses[0].id);
            }
        },

        editAddress(address) {
            this.addressSelectedForEdit = address;
            this.addDialogShow = true;
        },
        rechargeDialogClosed() {
            this.rechargeDialogShow = false;
        },
        paymentSelected(event, paymentMethod) {
            this.selectedPaymentMethod = paymentMethod;
        },
        walletSelected() {
            if (this.currentUser.balance >= this.totalPrice) {
                this.selectedPaymentMethod = {
                    code: "wallet",
                };
            } else {
                this.snack({
                    message: `You don't have enough wallet balance. Please recharge.`,
                    color: "red",
                });
            }
        },
        shippingAddressSelected(address_id) {
            this.getShippingCost(address_id);
        },
        selectShippingAddress(address_id) {
            this.selectedShippingAddressId = address_id;
            this.selectedBillingAddressId = address_id;
            this.getShippingCost(address_id);
        },
        async getShippingCost(address_id) {
            const res = await this.call_api(
                "get",
                `checkout/get-shipping-cost/${address_id}`
            );
            this.selectedDeliveryOption = res.data.success ? "standard" : "";
            this.standardDeliveryCost = parseFloat(
                res.data.standard_delivery_cost
            );
            this.expressDeliveryCost = parseFloat(
                res.data.express_delivery_cost
            );
        },
        async proceedCheckout() {
            if (this.demoMode) {
                this.snack({
                    message: "Data chaning action is not allowed in demo mode.",
                    color: "red",
                });
                return;
            }

            if (!this.checkbox) {
                this.snack({
                    message: `You need to agree with our policies`,
                    color: "red",
                });
                return;
            }
            if (this.getSelectedCartIds.length == 0) {
                this.snack({
                    message: `Please select a cart product`,
                    color: "red",
                });
                return;
            }
            if (
                this.selectedDeliveryType == "home_delivery" &&
                this.getAddresses.length == 0
            ) {
                this.snack({
                    message: `Please add a delivery address`,
                    color: "red",
                });
                return;
            }

            if (
                this.selectedDeliveryType === "home_delivery" &&
                !this.selectedShippingAddressId
            ) {
                this.snack({
                    message: `Please select a delivery address`,
                    color: "red",
                });
                return;
            }
            // Auto-use shipping address as billing address
            if (!this.selectedBillingAddressId && this.selectedShippingAddressId) {
                this.selectedBillingAddressId = this.selectedShippingAddressId;
            }
            
            // Removed: Billing address validation - automatically uses shipping address
            // if (!this.selectedBillingAddressId) {
            //     this.snack({
            //         message: `Please select a billing address`,
            //         color: "red",
            //     });
            //     return;
            // }

            if (this.selectedDeliveryType === "") {
                this.snack({
                    message: `Please select delivery type at first`,
                    color: "red",
                });
                return;
            }
            if (
                this.selectedDeliveryType === "pickup" &&
                this.for_pickup == false
            ) {
                this.snack({
                    message: `One or more items in the cart are not available for pickup`,
                    color: "red",
                });
                return;
            }
            if (
                this.selectedDeliveryType === "pickup" &&
                this.selectedPickupPoint == null
            ) {
                this.snack({
                    message: `Please select a pick up point`,
                    color: "red",
                });
                return;
            }
            // Removed: Delivery option validation - allow checkout even without delivery option
            // if (
            //     this.selectedDeliveryType === "home_delivery" &&
            //     this.selectedDeliveryOption === ""
            // ) {
            //     this.snack({
            //         message: `Sorry, delivery is not available in this shipping address.`,
            //         color: "red",
            //     });
            //     return;
            // }

            if (!this.selectedPaymentMethod) {
                this.snack({
                    message: `Please select a payment method`,
                    color: "red",
                });
                return;
            }

            if (
                this.selectedPaymentMethod &&
                this.selectedPaymentMethod.code.includes("offline_payment") &&
                this.transactionId === null
            ) {
                this.snack({
                    message: this.$i18n.t("please_input_transaction_id"),
                    color: "red",
                });
                return;
            }

            if (!this.checkShopMinOrder.success) {
                this.snack({
                    message: this.checkShopMinOrder.message,
                    color: "red",
                });
                return;
            }

            let formData = new FormData();
            formData.append(
                "shipping_address_id",
                this.selectedShippingAddressId
            );
            formData.append(
                "billing_address_id",
                this.selectedBillingAddressId
            );
            formData.append("payment_type", this.selectedPaymentMethod.code);
            formData.append("delivery_type", this.selectedDeliveryOption);
            formData.append("type_of_delivery", this.selectedDeliveryType);
            formData.append("pickup_point_id", this.selectedPickupPoint);

            let cartIds = this.getSelectedCartIds;
            cartIds.forEach((item, index) => {
                formData.append("cart_item_ids[]", item);
            });

            let coupon_codes = this.getAllCouponCodes;
            coupon_codes.forEach((couponItem, couponItemIndex) => {
                formData.append("coupon_codes[]", couponItem);
            });

            formData.append("transactionId", this.transactionId);
            formData.append("receipt", this.receipt);

            // console.log(this.receipt);
            // return;
            if (this.getCartPrice > 0) {
                this.checkoutLoading = true;
                const res = await this.call_api(
                    "post",
                    "checkout/order/store",
                    formData,
                    true
                );
                // console.log(res.data);
                if (res.data.success) {
                    if (res.data.payment_method == "wallet") {
                        this.deductFromWallet(res.data.grand_total);
                    }

                    if (res.data.go_to_payment) {
                        this.$refs.makePayment.pay({
                            requestedFrom: "/checkout",
                            paymentAmount: 0,
                            paymentMethod: res.data.payment_method,
                            paymentType: "cart_payment",
                            userId: this.currentUser.id,
                            oderCode: res.data.order_code,

                            // Authorize Net
                            card_number: this.authorizeNet.card_number,
                            cvv: this.authorizeNet.cvv,
                            expiration_month:
                                this.authorizeNet.expiration_month,
                            expiration_year: this.authorizeNet.expiration_year,
                        });
                    } else {
                        this.$router
                            .push({
                                name: "OrderConfirmed",
                                query: { orderCode: res.data.order_code },
                            })
                            .catch(() => {});
                    }
                    setTimeout(() => {
                        this.resetCoupon();
                        this.removeMultipleFromCart(this.getSelectedCartIds);
                    }, 2000);
                } else {
                    this.snack({
                        message: res.data.message,
                        color: "red",
                    });
                }
                this.checkoutLoading = false;
            }
        },
    },
    async created() {
        await this.fetchPickupPoints();
        await this.fetchAddresses();
        this.selectedShippingAddressId = this.getDefaultShippingAddress.id;
        // Auto-use shipping address as billing address
        this.selectedBillingAddressId = this.getDefaultShippingAddress.id;
        this.getShippingCost(this.selectedShippingAddressId);

        let dateArray = [];
        let i = new Date().getFullYear();
        for (i; i <= new Date().getFullYear() + 15; i++) {
            dateArray.push(i);
        }
        this.dateLoop = dateArray;
    },
    mounted() {
        if (this.$route.query.cart_payment && this.$route.query.order_code) {
            if (this.$route.query.cart_payment == "success") {
                this.$router
                    .push({
                        name: "OrderConfirmed",
                        query: {
                            orderCode: this.$route.query.order_code,
                        },
                    })
                    .catch(() => {});
                this.snack({ message: "Payment successful!" });
            } else if (this.$route.query.cart_payment == "failed") {
                this.$refs.failedPayment.open({
                    orderCode: this.$route.query.order_code,
                    paymentMethod: this.$route.query.payment_method,
                });
            }
        }
        this.rechargeWallet(this.$route.query.wallet_payment);
        this.fetchCartProducts();
    },
};
</script>

<style scoped>
.checkout-shopee-style .address-section,
.checkout-shopee-style .products-section,
.checkout-shopee-style .order-summary-section,
.checkout-shopee-style .payment-section,
.checkout-shopee-style .terms-section,
.checkout-shopee-style .checkout-footer {
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.checkout-shopee-style .selected-address {
    background: #fff;
    padding: 12px;
    border-radius: 4px;
}

.checkout-shopee-style .address-option:hover {
    background-color: #f5f5f5;
}

.checkout-shopee-style .product-item {
    transition: background-color 0.2s;
}

.checkout-shopee-style .product-item:hover {
    background-color: #fafafa;
}

.checkout-shopee-style .delivery-option-item {
    transition: all 0.2s;
}

.checkout-shopee-style .delivery-option-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checkout-shopee-style .payment-method-card .transition {
    transition: all 0.2s ease;
}

.checkout-shopee-style .payment-method-card:hover .border {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checkout-shopee-style .wallet-section {
    transition: all 0.2s ease;
}

.checkout-shopee-style .wallet-section:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checkout-shopee-style .sticky-bottom {
    position: sticky;
    bottom: 0;
    z-index: 10;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);
}

.checkout-shopee-style .products-header {
    font-weight: 500;
    color: #757575;
    font-size: 14px;
}

.checkout-shopee-style .cursor-pointer {
    cursor: pointer;
}

.checkout-shopee-style .gap-2 {
    gap: 8px;
}

.checkout-shopee-style .bg-soft-primary {
    background-color: rgba(var(--v-primary-base), 0.05) !important;
}

@media (max-width: 600px) {
    .checkout-shopee-style .product-item {
        flex-direction: column;
    }

    .checkout-shopee-style .checkout-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        border-radius: 0;
        margin: 0;
    }

    .checkout-shopee-style .delivery-option-item {
        margin-bottom: 8px;
    }
}

@media (min-width: 600px) {
    .checkout-banner img {
        height: 281px;
        object-fit: cover;
    }
}
</style>
