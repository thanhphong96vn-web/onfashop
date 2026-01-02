<template>
    <div>
        <!-- Cart drawer for all screen sizes -->
        <v-navigation-drawer
            class="cart-drawer"
            location="right"
            width="400"
            height="100vh"
            v-model="cartDrawerOpen"
            fixed
            temporary
            right
            clipped
            @update:modelValue="updateCartDrawer"
        >
            <cart-for-multi v-if="is_addon_activated('multi_vendor')" />
            <cart-for-single v-else />
        </v-navigation-drawer>
    </div>
</template>

<script>
import { mapActions, mapGetters, mapMutations } from "vuex";
import CartForMulti from "./CartForMulti.vue";
import CartForSingle from "./CartForSingle.vue";
export default {
    components: { CartForMulti, CartForSingle },
    computed: {
        ...mapGetters("cart", [
            "getCartCount",
            "getCartPrice",
            "getTotalCouponDiscount",
        ]),
        ...mapGetters("auth", ["cartDrawerOpen", "currentUser"]),
    },
    methods: {
        ...mapActions("cart", ["fetchCartProducts"]),
        ...mapMutations("auth", ["updateCartDrawer"]),
    },
    created() {
        this.fetchCartProducts();
    },
};
</script>

<style scoped>
.cart-drawer {
    z-index: 610;
}
</style>
