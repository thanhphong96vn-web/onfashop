<template>
    <div class="home-category-list" v-if="hasData">
        <v-container class="pa-0">
            <div
                class="shopee-header-section home-category-list__header shopee-header-section--simple"
            >
                <div class="shopee-header-section__header">
                    <div class="shopee-header-section__header__title">
                        DANH MỤC
                    </div>
                </div>
                <div class="shopee-header-section__content">
                    <!-- Loading Carousel -->
                    <div v-if="loading" class="category-carousel-wrapper">
                        <swiper
                            :slides-per-view="4"
                            :space-between="0"
                            :modules="modules"
                            :navigation="false"
                            :breakpoints="carouselOption.breakpoints"
                            class="category-swiper"
                            @swiper="onSwiper"
                        >
                            <swiper-slide
                                v-for="i in 4"
                                :key="i"
                                class="category-slide"
                            >
                                <div class="category-item">
                                    <div class="category-card">
                                        <v-skeleton-loader
                                            type="image"
                                            width="50"
                                            height="50"
                                            class="category-skeleton"
                                        ></v-skeleton-loader>
                                        <v-skeleton-loader
                                            type="text"
                                            width="60"
                                            class="mt-2"
                                        ></v-skeleton-loader>
                                    </div>
                                </div>
                            </swiper-slide>
                        </swiper>
                        <!-- Custom Navigation Buttons -->
                        <button 
                            @click="slidePrev" 
                            class="custom-nav-button custom-nav-prev"
                            :class="{ 'is-disabled': isBeginning }"
                        >
                            <span class="nav-arrow">‹</span>
                        </button>
                        <button 
                            @click="slideNext" 
                            class="custom-nav-button custom-nav-next"
                            :class="{ 'is-disabled': isEnd }"
                        >
                            <span class="nav-arrow">›</span>
                        </button>
                    </div>
                    
                    <!-- Category Carousel -->
                    <div v-else class="category-carousel-wrapper">
                        <swiper
                            :slides-per-view="4"
                            :space-between="0"
                            :modules="modules"
                            :navigation="false"
                            :breakpoints="carouselOption.breakpoints"
                            class="category-swiper"
                            @swiper="onSwiper"
                            @slideChange="onSlideChange"
                        >
                            <swiper-slide
                                v-for="(category, i) in categories"
                                :key="i"
                                class="category-slide"
                            >
                                <router-link
                                    class="category-item"
                                    :to="{
                                        name: 'Category',
                                        params: { categorySlug: category.slug },
                                    }"
                                >
                                    <div class="category-card">
                                        <div class="category-icon">
                                            <picture>
                                                <img
                                                    :src="getCategoryImage(category)"
                                                    :alt="category.name"
                                                    @error="imageFallback($event)"
                                                    @load="imageLoaded($event)"
                                                    class="category-image"
                                                    loading="lazy"
                                                />
                                            </picture>
                                        </div>
                                        <div class="category-name">
                                            {{ category.name }}
                                        </div>
                                    </div>
                                </router-link>
                            </swiper-slide>
                        </swiper>
                        <!-- Custom Navigation Buttons -->
                        <button 
                            @click="slidePrev" 
                            class="custom-nav-button custom-nav-prev"
                            :class="{ 'is-disabled': isBeginning }"
                        >
                            <span class="nav-arrow">‹</span>
                        </button>
                        <button 
                            @click="slideNext" 
                            class="custom-nav-button custom-nav-next"
                            :class="{ 'is-disabled': isEnd }"
                        >
                            <span class="nav-arrow">›</span>
                        </button>
                    </div>
                </div>
            </div>
        </v-container>
    </div>
</template>

<script>
import { Navigation } from "swiper/modules";
import { Swiper, SwiperSlide } from "swiper/vue";

export default {
    components: {
        Swiper,
        SwiperSlide,
    },
    setup() {
        return {
            modules: [Navigation],
        };
    },
    data: () => ({
        loading: true,
        categories: [],
        swiperInstance: null,
        isBeginning: true,
        isEnd: false,
        carouselOption: {
            breakpoints: {
                0: {
                    slidesPerView: 2,
                },
                600: {
                    slidesPerView: 3,
                },
                960: {
                    slidesPerView: 4,
                },
                1264: {
                    slidesPerView: 4,
                },
            },
        },
    }),
    computed: {
        hasData() {
            // Show section if loading, or if has categories
            return this.loading || (this.categories && this.categories.length > 0);
        },
    },
    async created() {
        const res = await this.call_api(
            "get",
            "setting/home/popular_categories"
        );
        if (res.data.success) {
            this.categories = res.data.data.data || [];
            // Debug: Log để kiểm tra dữ liệu
            console.log('Categories loaded:', this.categories);
            this.loading = false;
        }
    },
    methods: {
        onSwiper(swiper) {
            this.swiperInstance = swiper;
            this.isBeginning = swiper.isBeginning;
            this.isEnd = swiper.isEnd;
        },
        onSlideChange(swiper) {
            this.isBeginning = swiper.isBeginning;
            this.isEnd = swiper.isEnd;
        },
        slideNext() {
            if (this.swiperInstance) {
                this.swiperInstance.slideNext();
            }
        },
        slidePrev() {
            if (this.swiperInstance) {
                this.swiperInstance.slidePrev();
            }
        },
        getCategoryImage(category) {
            if (!category) {
                return this.static_asset('/assets/img/placeholder.jpg');
            }
            
            // Kiểm tra banner và icon, loại bỏ chuỗi rỗng, null, undefined
            const banner = category.banner && 
                          category.banner !== null && 
                          category.banner !== undefined &&
                          category.banner !== 'null' &&
                          category.banner !== 'undefined' &&
                          String(category.banner).trim() !== '' ? category.banner : null;
            
            const icon = category.icon && 
                        category.icon !== null && 
                        category.icon !== undefined &&
                        category.icon !== 'null' &&
                        category.icon !== 'undefined' &&
                        String(category.icon).trim() !== '' ? category.icon : null;
            
            // Ưu tiên banner, sau đó icon, cuối cùng là placeholder
            const imageUrl = banner || icon;
            
            if (imageUrl) {
                // Nếu URL đã đầy đủ (bắt đầu với http:// hoặc https://), trả về nguyên
                if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
                    return imageUrl;
                }
                // Nếu là relative path, trả về nguyên (đã được xử lý từ API)
                return imageUrl;
            }
            
            return this.static_asset('/assets/img/placeholder.jpg');
        },
        imageFallback(event) {
            // Chỉ set placeholder 1 lần, tránh infinite loop
            if (!event.target.dataset.fallbackLoaded) {
                event.target.dataset.fallbackLoaded = "true";
                const placeholder = this.static_asset('/assets/img/placeholder.jpg');
                if (event.target.src !== placeholder && !event.target.src.includes(placeholder)) {
                    event.target.src = placeholder;
                }
                // Remove error handler để không trigger lại
                event.target.onerror = null;
            }
        },
        imageLoaded(event) {
            // Đảm bảo ảnh hiển thị khi load xong
            event.target.style.opacity = "1";
        },
    },
};
</script>

<style scoped>
.home-category-list {
    background-color: #fff;
    margin-bottom: 20px;
    padding: 0;
    overflow: visible;
}

.home-category-list .v-container {
    max-width: 1200px;
}

.shopee-header-section {
    position: relative;
    overflow: visible;
}

.shopee-header-section--simple .shopee-header-section__header {
    padding: 16px;
    margin-bottom: 0;
}

@media (min-width: 960px) {
    .shopee-header-section--simple .shopee-header-section__header {
        padding: 16px 15px;
    }
}

.shopee-header-section__header__title {
    font-size: 14px;
    font-weight: 400;
    color: #333;
    line-height: 1.4;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

@media (min-width: 960px) {
    .shopee-header-section__header__title {
        font-size: 15px;
    }
}

.shopee-header-section__content {
    position: relative;
    overflow: visible;
    padding: 0;
}

/* Carousel Wrapper - Overflow visible để buttons hiện đầy đủ */
.category-carousel-wrapper {
    position: relative;
    overflow: visible;
    padding: 0 16px;
}

@media (min-width: 960px) {
    .category-carousel-wrapper {
        padding: 0 15px;
    }
}

/* Swiper Container - Overflow hidden để ẩn items thừa */
.category-swiper {
    overflow: hidden;
    position: relative;
    margin: 0;
    padding: 0;
}

.category-swiper :deep(.swiper-wrapper) {
    align-items: stretch;
    border-left: 1px solid #e5e5e5;
    border-top: 1px solid #e5e5e5;
}

.category-slide {
    height: auto !important;
    display: flex;
}

.category-swiper :deep(.swiper-slide) {
    height: auto !important;
}

/* Category Item */
.category-item {
    display: block;
    text-decoration: none;
    color: inherit;
    border: 1px solid #e5e5e5;
    border-left: none;
    background-color: #fff;
    transition: all 0.2s ease;
    width: 100%;
    height: 100%;
}

.category-item:first-child {
    border-left: 1px solid #e5e5e5;
}

.category-item:hover {
    border-color: #d0d0d0;
    background-color: #f5f5f5;
    box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.05);
}

/* Category Card */
.category-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px 8px;
    min-height: 100px;
    height: 100%;
}

@media (min-width: 600px) {
    .category-card {
        padding: 16px 12px;
        min-height: 110px;
    }
}

@media (min-width: 960px) {
    .category-card {
        padding: 20px 12px;
        min-height: 120px;
    }
}

/* Category Icon */
.category-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 8px;
    flex-shrink: 0;
}

@media (min-width: 600px) {
    .category-icon {
        width: 55px;
        height: 55px;
    }
}

@media (min-width: 960px) {
    .category-icon {
        width: 60px;
        height: 60px;
        margin-bottom: 10px;
    }
}

.category-icon picture {
    display: block;
    width: 100%;
    height: 100%;
}

.category-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    opacity: 1;
    transition: opacity 0.3s ease, transform 0.2s ease;
}

.category-item:hover .category-image {
    transform: scale(1.05);
}

/* Category Name */
.category-name {
    text-align: center;
    width: 100%;
    font-size: 11px;
    color: #333;
    line-height: 1.3;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 28px;
}

@media (min-width: 600px) {
    .category-name {
        font-size: 12px;
        min-height: 30px;
    }
}

@media (min-width: 960px) {
    .category-name {
        font-size: 13px;
        min-height: 32px;
    }
}

/* Custom Navigation Buttons - Nằm ngoài swiper container */
.custom-nav-button {
    position: absolute;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.95);
    border: none;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 30;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-nav-button:hover {
    background-color: #fff;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
}

.custom-nav-button.is-disabled {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

.custom-nav-next {
    right: 0px;
}

.custom-nav-prev {
    left: 0px;
}

.nav-arrow {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    line-height: 1;
    user-select: none;
}

/* Skeleton Loader */
.category-skeleton {
    border-radius: 0;
}
</style>
