<template>
    <div class="ps-lg-7 pt-4">
        <h1 class="fs-24 fw-700 opacity-80 mb-5 mt-3">
            {{ $t("my_querries") }}
        </h1>
        <div v-if="loading">
            <div class="text-center">
                <div>{{ $t("loading_please_wait") }}</div>
            </div>
        </div>
        <div v-else>
            <v-card v-if="getProductQuerries.length > 0">
                <div
                    v-for="(conversation, i) in getProductQuerries"
                    :key="i"
                    :class="[
                        'py-2',
                        { 'border-bottom': i + 1 != getProductQuerries.length },
                    ]"
                >
                    <ConversationBox :conversation="conversation" />
                </div>
            </v-card>
            <div v-else class="text-center">
                <div>{{ $t("no_conversation_found") }}</div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions } from "vuex";
import ConversationBox from "../../components/conversation/ConversationBox.vue";
export default {
    data: () => ({
        loading: true,
    }),
    components: {
        ConversationBox,
    },
    computed: {
        ...mapGetters("app", ["getProductQuerries"]),
    },
    methods: {
        ...mapActions("app", ["fetchProductQuerries"]),
    },
    async created() {
        // Fetch conversations when component is created
        this.loading = true;
        await this.fetchProductQuerries();
        this.loading = false;
    },
};
</script>
