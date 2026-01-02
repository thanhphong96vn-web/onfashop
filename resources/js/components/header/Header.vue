<template>
    <header
        :class="[
            'header-sticky ',
            { 'sticky-top': generalSettings.sticky_header == 1 },
        ]"
    >
        <TopBar :loading="loading" :data="data" />
        <LogoBar :loading="loading" :data="data" />
        <HeaderMenu :loading="loading" :data="data" />
    </header>
</template>

<script>
import { mapGetters } from "vuex";
import HeaderMenu from "./HeaderMenu.vue";
import LogoBar from "./LogoBar.vue";
import TopBar from "./TopBar.vue";
export default {
    data: () => ({
        loading: true,
        data: {},
    }),
    components: {
        TopBar,
        LogoBar,
        HeaderMenu,
    },
    computed: {
        ...mapGetters("app", ["generalSettings"]),
    },
    methods: {
        async getDetails() {
            const res = await this.call_api("get", `setting/header`);

            if (res.status === 200) {
                this.data = res.data;
                this.loading = false;
            }
        },
    },
    created() {
        this.getDetails();
    },
};
</script>

<style scoped>
.header-sticky {
    position: sticky !important;
    top: 0 !important;
    transform: translateY(0) !important;
    transition: none !important;
}

.header-sticky.sticky-top {
    position: sticky !important;
    top: 0 !important;
    transform: translateY(0) !important;
}
</style>