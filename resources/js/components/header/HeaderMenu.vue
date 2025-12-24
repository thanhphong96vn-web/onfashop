<template>
    <div class="header-menu-container">
        <v-container class="py-0">
            <div class="d-flex align-center justify-center py-2">
                <template v-if="loading">
                    <v-skeleton-loader
                        type="text"
                        class="w-100px px-3 mt-2"
                    ></v-skeleton-loader>
                    <v-skeleton-loader
                        type="text"
                        class="w-100px px-3 mt-2"
                    ></v-skeleton-loader>
                    <v-skeleton-loader
                        type="text"
                        class="w-100px px-3 mt-2"
                    ></v-skeleton-loader>
                    <v-skeleton-loader
                        type="text"
                        class="w-100px px-3 mt-2"
                    ></v-skeleton-loader>
                    <v-skeleton-loader
                        type="text"
                        class="w-100px px-3 mt-2"
                    ></v-skeleton-loader>
                </template>
                <template v-else>
                    <v-list class="d-flex py-0 header-menu-list">
                        <!--  Mega Menu -->
                        <v-list-item
                            class="flex-grow-0 flex-fill"
                            color="primary"
                        >
                            <v-list-item-title color="primary">
                                <MegaMenu color="primary"></MegaMenu>
                            </v-list-item-title>
                        </v-list-item>
                        <!--  -->

                        <v-list-item
                            v-for="(link, label, i) in data.header_menu"
                            :key="i"
                            class="flex-grow-0 flex-fill"
                        >
                            <v-list-item-title>
                                <dynamic-link
                                    :to="link"
                                    append-class="text-reset fs-13 fw-700 opacity-80"
                                >
                                    {{ label }}
                                </dynamic-link>
                            </v-list-item-title>
                        </v-list-item>
                    </v-list>
                </template>
            </div>
            <v-divider class=""></v-divider>
        </v-container>
    </div>
</template>

<script>
import MegaMenu from "./MegaMenu.vue";
export default {
    components: {
        MegaMenu,
    },

    props: {
        loading: { type: Boolean, required: true, default: true },
        data: {
            type: Object,
            default: {},
        },
    },
};
</script>

<style scoped>
.header-menu-container {
    background: var(
        --color-primary-gradient,
        linear-gradient(to right, #fdcc34, #ffd562)
    );
}

.header-menu-container :deep(.v-container) {
    background: transparent !important;
}

.header-menu-container :deep(.v-list) {
    background: transparent !important;
}

.header-menu-container :deep(.v-list-item) {
    background: transparent !important;
}

/* Responsive cho mobile */
@media (max-width: 959px) {
    .header-menu-list {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    
    .header-menu-list::-webkit-scrollbar {
        height: 4px;
    }
    
    .header-menu-list::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
    }
    
    .header-menu-list::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.3);
        border-radius: 2px;
    }
    
    .header-menu-list :deep(.v-list-item) {
        flex: 0 0 auto;
        white-space: nowrap;
        min-width: fit-content;
        padding-left: 12px !important;
        padding-right: 12px !important;
    }
}
</style>
