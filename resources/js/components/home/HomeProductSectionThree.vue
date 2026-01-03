<template>
    <div class="mb-5" v-if="hasData">
        <v-container class="py-0">
            <h2 class="mb-4">{{ title }}</h2>
            <div v-if="loading">
                <v-row class="gutters-10">
                    <v-col 
                        cols="6" 
                        sm="4" 
                        md="3" 
                        lg="2" 
                        v-for="i in 24" 
                        :key="i"
                    >
                        <v-skeleton-loader type="image" height="186" />
                    </v-col>
                </v-row>
            </div>
            <div v-else>
                <v-row class="gutters-10">
                    <v-col 
                        cols="6" 
                        sm="4" 
                        md="3" 
                        lg="2" 
                        v-for="(product, i) in displayedProducts" 
                        :key="i"
                    >
                        <product-box :product-details="product" :is-loading="loading" />
                    </v-col>
                </v-row>
                <div class="text-center mt-6 mb-4" v-if="products.length > displayedProductsCount">
                    <router-link 
                        :to="{ name: 'Shop' }" 
                        class="view-more-btn"
                    >
                        Xem thêm
                    </router-link>
                </div>
            </div>
        </v-container>
    </div>
</template>

<script>
export default {
    data: () => ({
        loading: true,
        title: "",
        banner: {},
        products: [],
        displayedProductsCount: 24, // 6 cột x 4 hàng
    }),
    computed: {
        hasData() {
            // Show section if loading, or if has products or banner
            return this.loading || (this.products && this.products.length > 0) || (this.banner && this.banner.img);
        },
        displayedProducts() {
            // Chỉ hiển thị 24 sản phẩm đầu tiên (6 cột x 4 hàng)
            return this.products.slice(0, this.displayedProductsCount);
        },
    },
    async created() {
        const res = await this.call_api(
            "get",
            "setting/home/product_section_three"
        );
        if (res.data.success) {
            this.title = res.data.data.title;
            this.banner = res.data.data.banner;
            this.products = res.data.data.products.data;
            this.loading = false;
        }
    },
};
</script>
<style scoped>
h2 {
    font-size: 16px;
}
@media (min-width: 960px) {
    h2 {
        font-size: 24px;
    }
}
.view-more-btn {
    display: inline-block;
    padding: 12px 32px;
    min-width: 200px;
    background-color: #fdcc35;
    color: #000000 !important;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
}
.view-more-btn:hover {
    background-color: #e23d1d;
    color: #ffffff !important;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(238, 77, 45, 0.3);
}
</style>
