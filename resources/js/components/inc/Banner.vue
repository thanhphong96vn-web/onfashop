<template>
    <div>
        <template v-if="loading">
            <v-skeleton-loader type="image" height="145"></v-skeleton-loader>
        </template>
        <template v-else>
            <dynamic-link
                :to="banner.link"
                append-class="text-reset d-block lh-0"
            >
                <img 
                    :src="banner.img || static_asset('/assets/img/placeholder.jpg')" 
                    class="img-fluid w-100" 
                    :alt="appName"
                    @error="imageFallback($event)"
                />
            </dynamic-link>
        </template>
    </div>
</template>

<script>
import { mapGetters } from "vuex";
export default {
    props: {
        loading: { type: Boolean, required: true, default: true },
        banner: {
            type: Object,
            required: true,
        },
    },
    computed: {
        ...mapGetters("app", ["appName"]),
    },
    methods: {
        imageFallback(event) {
            const placeholder = this.static_asset('/assets/img/placeholder.jpg');
            if (event.target.src !== placeholder && !event.target.src.includes(placeholder)) {
                event.target.src = placeholder;
            }
        },
    },
};
</script>
