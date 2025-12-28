<template>
    <!-- Only show if user is authenticated -->
    <div
        v-if="
            isAuthenticated &&
            generalSettings.conversation_system == 1 &&
            isMultiVendorActivated
        "
    >
        <!-- Chat Button (when window is closed) -->
        <button
            v-if="!chatWindowOpen"
            class="seller-chat-button text-center fw-500 fs-12 px-6 py-4 btn-primary d-none d-lg-block"
            type="button"
            @click.stop="openChatWindow"
        >
            <span class="d-flex align-center white-text">
                <img :src="static_asset('/assets/img/chat.svg')" height="30" />
                <span class="fs-14 fw-700 ms-2">{{
                    $t("chat_with_seller") || "Chat với Shop"
                }}</span>
                <span
                    v-if="unseenCount > 0"
                    class="ms-2 badge badge-danger rounded-pill"
                    >{{ unseenCount }}</span
                >
                <i class="las la-angle-up ms-auto"></i>
            </span>
        </button>

        <!-- Chat Window (2 panel layout) -->
        <div
            :class="[
                'has-transition white seller-chat-window',
                chatWindowOpen ? 'd-block' : 'd-none',
            ]"
        >
            <!-- Header (Yellow bar) -->
            <div class="seller-chat-header d-flex align-center pa-3">
                <div class="d-flex align-center flex-grow-1">
                    <i class="las la-comments fs-20 me-2"></i>
                    <span class="fs-16 fw-700"
                        >CHAT ({{ conversations.length }})</span
                    >
                </div>
                <button
                    class="ms-auto btn-close-chat"
                    type="button"
                    @click.stop="closeChatWindow"
                >
                    <i class="las la-times fs-20"></i>
                </button>
            </div>

            <!-- Main Content (2 panels) -->
            <div class="seller-chat-content d-flex">
                <!-- Left Panel: Conversation List -->
                <div class="seller-chat-list-panel">
                    <div class="seller-chat-list-header pa-3 border-bottom">
                        <h6 class="mb-0 fw-600">
                            {{ $t("conversations") || "Danh sách chat" }}
                        </h6>
                    </div>
                    <div class="seller-chat-list-body c-scrollbar">
                        <div
                            v-if="loadingConversations"
                            class="text-center pa-4"
                        >
                            <div>{{ $t("loading_please_wait") }}</div>
                        </div>
                        <div
                            v-else-if="conversations.length === 0"
                            class="text-center pa-4"
                        >
                            <div class="opacity-60">
                                {{
                                    $t("no_conversation_found") ||
                                    "Chưa có cuộc trò chuyện nào"
                                }}
                            </div>
                        </div>
                        <div v-else>
                            <div
                                v-for="(conv, index) in conversations"
                                :key="index"
                                :class="[
                                    'seller-chat-item pa-3 border-bottom cursor-pointer',
                                    {
                                        active:
                                            selectedConversation &&
                                            selectedConversation.id === conv.id,
                                    },
                                ]"
                                @click="selectConversation(conv)"
                            >
                                <div class="d-flex align-center">
                                    <v-avatar size="40" class="me-2">
                                        <img
                                            :src="
                                                conv.receiver_image ||
                                                static_asset(
                                                    '/assets/img/avatar-place.png'
                                                )
                                            "
                                            @error="imageFallback($event)"
                                        />
                                    </v-avatar>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-600 fs-14 text-truncate">
                                            {{
                                                conv.receiver_name ||
                                                conv.receiver_shop
                                            }}
                                        </div>
                                        <div
                                            class="fs-12 opacity-60 text-truncate"
                                        >
                                            {{ conv.latest_message_time }}
                                        </div>
                                        <div class="fs-13 text-truncate mt-1">
                                            {{
                                                conv.latest_message?.message ||
                                                ""
                                            }}
                                        </div>
                                    </div>
                                    <span
                                        v-if="conv.sender_viewed == 0"
                                        class="badge badge-danger rounded-circle ms-2"
                                        style="width: 8px; height: 8px"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Conversation Details -->
                <div class="seller-chat-detail-panel">
                    <div
                        v-if="!selectedConversation"
                        class="seller-chat-empty d-flex align-center justify-center h-100"
                    >
                        <div class="text-center">
                            <i
                                class="las la-comments fs-48 opacity-30 mb-3"
                            ></i>
                            <div class="opacity-60">
                                {{
                                    $t("select_conversation") ||
                                    "Chọn một cuộc trò chuyện để xem"
                                }}
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <!-- Detail Header -->
                        <div
                            class="seller-chat-detail-header pa-3 border-bottom d-flex align-center"
                        >
                            <v-avatar size="40" class="me-2">
                                <img
                                    :src="
                                        selectedConversation.receiver_image ||
                                        static_asset(
                                            '/assets/img/avatar-place.png'
                                        )
                                    "
                                    @error="imageFallback($event)"
                                />
                            </v-avatar>
                            <div class="flex-grow-1">
                                <div class="fw-600">
                                    {{
                                        selectedConversation.receiver_name ||
                                        selectedConversation.receiver_shop
                                    }}
                                </div>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div
                            class="seller-chat-messages c-scrollbar"
                            ref="messagesContainer"
                        >
                            <div
                                v-if="loadingMessages"
                                class="text-center pa-4"
                            >
                                <div>{{ $t("loading_please_wait") }}</div>
                            </div>
                            <div
                                v-else-if="currentMessages.length === 0"
                                class="text-center pa-4"
                            >
                                <div class="opacity-60">
                                    {{
                                        $t("no_messages") ||
                                        "Chưa có tin nhắn nào"
                                    }}
                                </div>
                            </div>
                            <div v-else>
                                <div
                                    v-for="(message, i) in currentMessages"
                                    :key="
                                        message.id ||
                                        `msg-${i}-${message.created_at}`
                                    "
                                    :class="[
                                        'seller-chat-message pa-3',
                                        {
                                            'own-message':
                                                message.user_id ===
                                                currentUser.id,
                                        },
                                    ]"
                                >
                                    <div
                                        v-if="
                                            message.user_id !== currentUser.id
                                        "
                                        class="d-flex align-start mb-2"
                                    >
                                        <v-avatar size="36" class="me-2">
                                            <img
                                                :src="
                                                    message.user_image ||
                                                    static_asset(
                                                        '/assets/img/avatar-place.png'
                                                    )
                                                "
                                                @error="imageFallback($event)"
                                            />
                                        </v-avatar>
                                        <div class="flex-grow-1">
                                            <div class="fw-600 fs-13 mb-1">
                                                {{ message.user_name }}
                                            </div>
                                            <div class="message-bubble">
                                                {{ message.message }}
                                            </div>
                                            <div class="fs-11 opacity-60 mt-1">
                                                {{ message.created_at }}
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        v-else
                                        class="d-flex align-start mb-2 justify-end"
                                    >
                                        <div class="flex-grow-1 text-end">
                                            <div class="message-bubble own">
                                                {{ message.message }}
                                            </div>
                                            <div class="fs-11 opacity-60 mt-1">
                                                {{ message.created_at }}
                                            </div>
                                        </div>
                                        <v-avatar size="36" class="ms-2">
                                            <img
                                                :src="
                                                    message.user_image ||
                                                    static_asset(
                                                        '/assets/img/avatar-place.png'
                                                    )
                                                "
                                                @error="imageFallback($event)"
                                            />
                                        </v-avatar>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div class="seller-chat-input-area pa-3 border-top">
                            <v-form @submit.prevent="sendMessage">
                                <div class="d-flex align-center">
                                    <v-text-field
                                        v-model="messageForm.message"
                                        :placeholder="
                                            $t('enter_message') ||
                                            'Nhập nội dung tin nhắn...'
                                        "
                                        variant="outlined"
                                        density="compact"
                                        hide-details
                                        class="flex-grow-1 me-2"
                                    ></v-text-field>
                                    <v-btn
                                        type="submit"
                                        color="primary"
                                        icon
                                        :loading="sending"
                                        :disabled="
                                            !messageForm.message ||
                                            !messageForm.message.trim()
                                        "
                                    >
                                        <i class="las la-paper-plane"></i>
                                    </v-btn>
                                </div>
                            </v-form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";

export default {
    data: () => ({
        loadingConversations: false,
        loadingMessages: false,
        sending: false,
        conversations: [],
        selectedConversation: null,
        currentMessages: [],
        messageForm: {
            message: "",
            conversation_id: null,
        },
        lastConversationsHash: null, // Track để so sánh
        lastMessagesCount: 0, // Track số lượng messages
    }),
    computed: {
        ...mapGetters("auth", [
            "chatWindowOpen",
            "currentUser",
            "isAuthenticated",
        ]),
        ...mapGetters("app", ["generalSettings", "getProductQuerries"]),
        unseenCount() {
            return this.conversations.filter((c) => c.sender_viewed == 0)
                .length;
        },
        isMultiVendorActivated() {
            return this.is_addon_activated("multi_vendor");
        },
    },
    watch: {
        chatWindowOpen(newValue) {
            if (newValue && this.isAuthenticated) {
                this.fetchConversations();
            }
        },
        selectedConversation: {
            handler(newConv) {
                if (newConv) {
                    this.messageForm.conversation_id = newConv.id;
                    this.fetchConversationDetails(newConv.slug);
                }
            },
            immediate: false,
        },
    },
    methods: {
        ...mapMutations("auth", ["updateChatWindow"]),
        ...mapActions("app", ["fetchProductQuerries"]),

        // Helper: Tạo hash đơn giản để so sánh conversations
        createConversationsHash(conversations) {
            return conversations
                .map((c) => {
                    // latest_message có thể là object hoặc string
                    const latestMsg = c.latest_message;
                    const msgText =
                        typeof latestMsg === "object"
                            ? latestMsg?.message || latestMsg?.id || ""
                            : latestMsg || "";
                    return {
                        id: c.id,
                        sender_viewed: c.sender_viewed,
                        latest_message: msgText,
                        latest_message_time: c.latest_message_time,
                    };
                })
                .join("|");
        },

        // Helper: Kiểm tra xem đang scroll ở cuối không
        isScrolledToBottom() {
            if (!this.$refs.messagesContainer) return true;
            const container = this.$refs.messagesContainer;
            const threshold = 100; // 100px từ cuối
            return (
                container.scrollHeight -
                    container.scrollTop -
                    container.clientHeight <
                threshold
            );
        },

        openChatWindow() {
            this.updateChatWindow(true);
            if (this.isAuthenticated) {
                this.fetchConversations();
            }
        },
        closeChatWindow() {
            this.updateChatWindow(false);
            this.selectedConversation = null;
            this.currentMessages = [];
            this.lastMessagesCount = 0;
            this.lastConversationsHash = null;
        },
        async fetchConversations(silent = false) {
            // Chỉ set loading khi user action, không phải auto-refresh
            if (!silent) {
                this.loadingConversations = true;
            }

            try {
                // Use existing API endpoint
                await this.fetchProductQuerries();
                // Get from store
                const newConversations = this.getProductQuerries || [];

                // So sánh bằng hash - nhanh hơn JSON.stringify
                const newHash = this.createConversationsHash(newConversations);

                if (newHash !== this.lastConversationsHash) {
                    // Có thay đổi - update
                    // Force Vue reactivity bằng cách tạo array mới
                    this.conversations = [...newConversations];
                    this.lastConversationsHash = newHash;

                    // Auto-select first conversation if available and none selected
                    if (
                        this.conversations.length > 0 &&
                        !this.selectedConversation
                    ) {
                        this.selectConversation(this.conversations[0]);
                    }

                    // Nếu đang có conversation được chọn, cập nhật lại selectedConversation
                    // để sync latest_message và timestamp, nhưng giữ nguyên slug
                    if (this.selectedConversation) {
                        const updatedConv = newConversations.find(
                            (c) => c.id === this.selectedConversation.id
                        );
                        if (updatedConv) {
                            // Giữ nguyên slug để có thể refresh messages
                            this.selectedConversation = {
                                ...updatedConv,
                                slug:
                                    this.selectedConversation.slug ||
                                    updatedConv.slug,
                            };
                        }
                    }
                }

                // Interval đã được xóa - sẽ dùng WebSocket sau này để auto-refresh messages
            } catch (error) {
                console.error("Error fetching conversations:", error);
                if (!silent) {
                    this.snack({
                        message: this.$i18n.t("something_went_wrong"),
                        color: "red",
                    });
                }
            } finally {
                if (!silent) {
                    this.loadingConversations = false;
                }
                // Interval đã được xóa - sẽ dùng WebSocket sau này
            }
        },
        async fetchConversationDetails(slug, silent = false) {
            if (!slug) return;

            // Chỉ set loading khi không phải silent refresh
            if (!silent) {
                this.loadingMessages = true;
            }

            try {
                const res = await this.call_api("get", `user/querries/${slug}`);
                if (res.data.success) {
                    const newMessages = res.data.data.messages || [];
                    const oldMessages = this.currentMessages || [];

                    // Luôn update để đảm bảo sync data (giống ConversationDetails.vue)
                    // Force Vue re-render bằng cách tạo array mới và object mới
                    const wasAtBottom = this.isScrolledToBottom();
                    const oldLength = oldMessages.length;
                    const newLength = newMessages.length;
                    const hasNewMessages = newLength > oldLength;

                    // Tạo array mới để force Vue reactivity - đảm bảo Vue detect được thay đổi
                    // Sử dụng JSON parse/stringify để đảm bảo tạo object hoàn toàn mới
                    // Điều này đảm bảo Vue 3 detect được thay đổi và re-render
                    this.currentMessages = JSON.parse(
                        JSON.stringify(newMessages)
                    );
                    this.selectedConversation = JSON.parse(
                        JSON.stringify(res.data.data)
                    );
                    this.messageForm.conversation_id = res.data.data.id;

                    // Chỉ scroll nếu đang ở cuối hoặc có message mới
                    if (wasAtBottom || hasNewMessages) {
                        this.$nextTick(() => {
                            this.scrollToBottom();
                        });
                    }
                    this.lastMessagesCount = newMessages.length;
                }
            } catch (error) {
                console.error("Error fetching conversation details:", error);
                if (!silent) {
                    this.snack({
                        message: this.$i18n.t("something_went_wrong"),
                        color: "red",
                    });
                }
            } finally {
                if (!silent) {
                    this.loadingMessages = false;
                }
            }
        },
        selectConversation(conversation) {
            this.selectedConversation = conversation;
            this.lastMessagesCount = 0; // Reset khi chọn conversation mới
            this.fetchConversationDetails(conversation.slug);
        },
        async sendMessage() {
            if (!this.messageForm.message || !this.messageForm.message.trim())
                return;
            if (!this.messageForm.conversation_id) return;

            this.sending = true;
            const messageText = this.messageForm.message.trim();

            try {
                const res = await this.call_api(
                    "post",
                    "user/new-message-query",
                    this.messageForm
                );
                if (res.data.success) {
                    this.messageForm.message = "";

                    // Refresh conversation details (không silent vì user vừa gửi)
                    if (this.selectedConversation) {
                        await this.fetchConversationDetails(
                            this.selectedConversation.slug,
                            false // Không silent - user action
                        );
                    }
                    // Refresh conversations list (không silent)
                    await this.fetchConversations(false);
                } else {
                    this.snack({
                        message:
                            res.data.message ||
                            this.$i18n.t("something_went_wrong"),
                        color: "red",
                    });
                }
            } catch (error) {
                console.error("Error sending message:", error);
                this.snack({
                    message: this.$i18n.t("something_went_wrong"),
                    color: "red",
                });
            } finally {
                this.sending = false;
            }
        },
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.messagesContainer) {
                    this.$refs.messagesContainer.scrollTop =
                        this.$refs.messagesContainer.scrollHeight;
                }
            });
        },
        // Interval đã được xóa - sẽ dùng WebSocket sau này để auto-refresh
    },
    created() {
        if (this.isAuthenticated && this.chatWindowOpen) {
            this.fetchConversations();
        }
    },
    beforeUnmount() {
        // Cleanup - interval đã được xóa, sẽ dùng WebSocket sau này
    },
};
</script>

<style scoped>
.seller-chat-button {
    z-index: 6;
    position: fixed;
    width: 270px;
    right: 50px;
    bottom: 0;
    border-radius: 5px 5px 0 0;
    overflow: hidden;
    box-shadow: 0 0px 20px rgba(0, 0, 0, 0.16);
}

.seller-chat-window {
    z-index: 1000;
    position: fixed;
    width: 800px;
    max-width: 90vw;
    right: 50px;
    bottom: 0;
    border-radius: 5px 5px 0 0;
    overflow: hidden;
    box-shadow: 0 0px 20px rgba(0, 0, 0, 0.16);
    background: white;
    height: 600px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.seller-chat-header {
    background: #ffc107; /* Yellow header */
    color: #000;
    min-height: 50px;
}

.seller-chat-content {
    flex: 1;
    overflow: hidden;
    display: flex;
    min-height: 0;
}

.seller-chat-list-panel {
    width: 300px;
    border-right: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    background: #f5f5f5;
    min-height: 0;
    overflow: hidden;
}

.seller-chat-list-header {
    background: white;
    min-height: 50px;
}

.seller-chat-list-body {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    background: white;
    min-height: 0;
    max-height: 100%;
    scroll-behavior: smooth;
}

.seller-chat-item {
    transition: background 0.2s;
}

.seller-chat-item:hover {
    background: #f0f0f0;
}

.seller-chat-item.active {
    background: #e3f2fd;
}

.seller-chat-detail-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: white;
    min-height: 0;
    overflow: hidden;
    position: relative; /* Để input area có thể position */
}

.seller-chat-detail-header {
    min-height: 60px;
    background: #fafafa;
}

.seller-chat-messages {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 16px;
    min-height: 0;
    max-height: 400px;
    scroll-behavior: smooth;
    flex-shrink: 1; /* Cho phép shrink để nhường chỗ cho input */
}

.seller-chat-message {
    margin-bottom: 8px;
}

.message-bubble {
    display: inline-block;
    padding: 8px 12px;
    border-radius: 12px;
    background: #f0f0f0;
    max-width: 70%;
    word-wrap: break-word;
}

.message-bubble.own {
    background: #1976d2;
    color: white;
}

.seller-chat-input-area {
    min-height: 70px;
    max-height: 120px;
    background: #fafafa;
    flex-shrink: 0; /* Không cho shrink */
    display: flex;
    align-items: center;
    z-index: 10; /* Đảm bảo luôn ở trên */
}

.seller-chat-empty {
    min-height: 400px;
}

.btn-close-chat {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
}

.btn-close-chat:hover {
    opacity: 0.7;
}

/* RTL Support */
.v-locale--is-rtl .seller-chat-button,
.v-locale--is-rtl .seller-chat-window {
    right: auto;
    left: 50px;
}

/* Mobile Responsive */
@media (max-width: 963px) {
    .seller-chat-window {
        width: 100%;
        right: 0;
        left: 0;
        height: 100vh !important;
        max-height: 100vh !important;
        border-radius: 0;
    }

    .v-locale--is-rtl .seller-chat-window {
        right: 0 !important;
        left: 0 !important;
    }

    .seller-chat-list-panel {
        width: 100%;
        border-right: none;
    }

    .seller-chat-detail-panel {
        display: none;
    }

    .seller-chat-detail-panel.active {
        display: flex;
    }
}
</style>
