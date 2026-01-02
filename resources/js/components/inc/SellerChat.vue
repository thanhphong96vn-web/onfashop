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
            class="seller-chat-button text-center fw-500 btn-primary d-none d-lg-block"
            type="button"
            @click.stop="openChatWindow"
        >
            <span class="d-flex align-center justify-center white-text">
                <i class="las la-comments fs-20"></i>
                <span class="fs-18 fw-600 ms-2">{{
                    getTranslation("chat", "Chat")
                }}</span>
                <span
                    v-if="unseenCount > 0"
                    class="ms-2 badge badge-danger rounded-pill"
                    style="
                        min-width: 18px;
                        height: 18px;
                        padding: 0 5px;
                        font-size: 22px;
                        line-height: 22px;
                    "
                    >{{ unseenCount }}</span
                >
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
                <div :class="['seller-chat-list-panel', { 'd-none-mobile': selectedConversation }]">
                    <div class="seller-chat-list-header pa-3 border-bottom">
                        <h4 class="mb-0 fw-600">
                            {{
                                getTranslation(
                                    "conversations",
                                    "Danh sách chat"
                                )
                            }}
                        </h4>
                    </div>
                    <div class="seller-chat-list-body c-scrollbar">
                        <div
                            v-if="loadingConversations"
                            class="text-center pa-4"
                        >
                            <div>
                                {{
                                    getTranslation(
                                        "loading_please_wait",
                                        "Đang tải, vui lòng đợi..."
                                    )
                                }}
                            </div>
                        </div>
                        <div
                            v-else-if="conversations.length === 0"
                            class="text-center pa-4"
                        >
                            <div class="opacity-60">
                                {{
                                    getTranslation(
                                        "no_conversation_found",
                                        "Chưa có cuộc trò chuyện nào"
                                    )
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
                <div :class="['seller-chat-detail-panel', { active: selectedConversation }]">
                    <div
                        v-if="!selectedConversation"
                        class="seller-chat-empty d-flex align-center justify-center h-100"
                    >
                        <div class="text-center">
                            <i
                                class="las la-comments opacity-30 mb-3"
                                style="font-size: 88px"
                            ></i>
                            <div class="opacity-60">
                                {{
                                    getTranslation(
                                        "select_conversation",
                                        "Chọn một cuộc trò chuyện để xem"
                                    )
                                }}
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <!-- Detail Header -->
                        <div
                            class="seller-chat-detail-header pa-3 border-bottom d-flex align-center"
                        >
                            <button
                                class="btn-back-list d-lg-none me-2"
                                type="button"
                                @click.stop="selectedConversation = null"
                            >
                                <i class="las la-arrow-left fs-20"></i>
                            </button>
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
                                <div>
                                    {{
                                        getTranslation(
                                            "loading_please_wait",
                                            "Đang tải, vui lòng đợi..."
                                        )
                                    }}
                                </div>
                            </div>
                            <div
                                v-else-if="currentMessages.length === 0"
                                class="text-center pa-4"
                            >
                                <div class="opacity-60">
                                    {{
                                        getTranslation(
                                            "no_messages",
                                            "Chưa có tin nhắn nào"
                                        )
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
                        <div class="seller-chat-input-area">
                            <form
                                @submit.prevent="sendMessage"
                                class="seller-chat-input-form"
                            >
                                <div class="seller-chat-input-wrapper">
                                    <textarea
                                        v-model="messageForm.message"
                                        class="seller-chat-input"
                                        :placeholder="
                                            getTranslation(
                                                'enter_message',
                                                'Nhập nội dung tin nhắn...'
                                            )
                                        "
                                        rows="1"
                                        @keydown.enter.exact.prevent="
                                            sendMessage
                                        "
                                        @keydown.shift.enter.exact="
                                            handleShiftEnter
                                        "
                                    ></textarea>
                                    <button
                                        type="submit"
                                        class="seller-chat-send-btn"
                                        :disabled="
                                            !messageForm.message ||
                                            !messageForm.message.trim() ||
                                            sending
                                        "
                                    >
                                        <i
                                            class="las la-paper-plane"
                                            v-if="!sending"
                                        ></i>
                                        <span
                                            v-else
                                            class="seller-chat-send-loading"
                                        ></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters, mapActions, mapMutations } from "vuex";
import echo from "../../echo";

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
        echoChannels: [], // Track các channels đã subscribe
        isInitialLoad: true, // Track lần đầu load conversation để không scroll tự động
        isFetchingConversations: false, // Flag để tránh fetch nhiều lần cùng lúc
        conversationCreatedTimeout: null, // Timeout cho debounce conversation created event
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
        "messageForm.message"() {
            this.resizeTextarea();
        },
    },
    methods: {
        getTranslation(key, fallback) {
            const translated = this.$t(key);
            // If translation returns the same key, it means translation not found
            if (translated === key || !translated) {
                return fallback;
            }
            return translated;
        },
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
            this.cleanupEchoChannels(); // Cleanup Echo channels
        },
        handleConversationCreated() {
            // Refresh conversations khi có conversation mới được tạo
            if (this.isAuthenticated && this.chatWindowOpen) {
                // Delay một chút để đảm bảo server đã xử lý xong
                // Sử dụng debounce để tránh gọi nhiều lần
                if (this.conversationCreatedTimeout) {
                    clearTimeout(this.conversationCreatedTimeout);
                }
                this.conversationCreatedTimeout = setTimeout(() => {
                    this.fetchConversations(true);
                    this.conversationCreatedTimeout = null;
                }, 1000);
            }
        },
        async fetchConversations(silent = false) {
            // Tránh fetch nhiều lần cùng lúc
            if (this.isFetchingConversations) {
                return;
            }

            this.isFetchingConversations = true;
            
            // Chỉ set loading khi user action, không phải auto-refresh
            if (!silent) {
                this.loadingConversations = true;
            }

            try {
                // Use existing API endpoint
                await this.fetchProductQuerries();
                // Get from store
                const rawConversations = this.getProductQuerries || [];

                // Loại bỏ duplicate conversations dựa trên receiver_id (shop)
                // Mỗi shop (receiver_id) chỉ hiển thị 1 conversation (conversation mới nhất)
                const conversationsByShop = new Map();
                for (const conv of rawConversations) {
                    if (!conv.receiver_id) {
                        // Nếu không có receiver_id, giữ nguyên (fallback: dùng ID)
                        const key = 'no_receiver_' + conv.id;
                        if (!conversationsByShop.has(key)) {
                            conversationsByShop.set(key, conv);
                        }
                        continue;
                    }
                    
                    // Tạo key dựa trên receiver_id (shop)
                    const shopKey = conv.receiver_id;
                    
                    const existing = conversationsByShop.get(shopKey);
                    if (!existing) {
                        conversationsByShop.set(shopKey, conv);
                    } else {
                        // So sánh timestamp để giữ conversation mới hơn
                        const existingTime = existing.latest_message_time || '';
                        const newTime = conv.latest_message_time || '';
                        
                        // Parse timestamp để so sánh (format: "h:i:m d-m-Y")
                        let shouldReplace = false;
                        if (newTime && existingTime) {
                            // So sánh string timestamp
                            if (newTime > existingTime) {
                                shouldReplace = true;
                            } else if (newTime === existingTime) {
                                // Nếu cùng timestamp, giữ conversation có ID lớn hơn (mới hơn)
                                if (conv.id > existing.id) {
                                    shouldReplace = true;
                                }
                            }
                        } else if (newTime && !existingTime) {
                            shouldReplace = true;
                        } else if (!newTime && !existingTime) {
                            // Nếu cả hai đều không có timestamp, giữ conversation có ID lớn hơn
                            if (conv.id > existing.id) {
                                shouldReplace = true;
                            }
                        }
                        
                        if (shouldReplace) {
                            conversationsByShop.set(shopKey, conv);
                        }
                    }
                }
                // Convert Map values to array và sort theo latest_message_time (mới nhất trước)
                const uniqueConversations = Array.from(conversationsByShop.values()).sort((a, b) => {
                    const timeA = a.latest_message_time || '';
                    const timeB = b.latest_message_time || '';
                    if (timeA && timeB) {
                        return timeB.localeCompare(timeA);
                    }
                    // Nếu không có timestamp, sort theo ID (lớn hơn = mới hơn)
                    return (b.id || 0) - (a.id || 0);
                });
                
                // Debug: Log nếu có duplicate
                if (rawConversations.length !== uniqueConversations.length) {
                    console.warn('Found duplicate conversations by shop:', {
                        raw: rawConversations.length,
                        unique: uniqueConversations.length,
                        duplicates: rawConversations.length - uniqueConversations.length,
                        rawConversations: rawConversations.map(c => ({ id: c.id, receiver_id: c.receiver_id, receiver_name: c.receiver_name || c.receiver_shop })),
                        uniqueConversations: uniqueConversations.map(c => ({ id: c.id, receiver_id: c.receiver_id, receiver_name: c.receiver_name || c.receiver_shop }))
                    });
                }

                // So sánh bằng hash - nhanh hơn JSON.stringify
                const newHash = this.createConversationsHash(uniqueConversations);

                if (newHash !== this.lastConversationsHash) {
                    // Có thay đổi - update
                    // Force Vue reactivity bằng cách tạo array mới
                    this.conversations = [...uniqueConversations];
                    this.lastConversationsHash = newHash;

                    // Auto-select first conversation if available and none selected (only on desktop)
                    // On mobile, show list first, let user select
                    if (
                        this.conversations.length > 0 &&
                        !this.selectedConversation &&
                        window.innerWidth > 963
                    ) {
                        this.selectConversation(this.conversations[0]);
                    }

                    // Nếu đang có conversation được chọn, cập nhật lại selectedConversation
                    // để sync latest_message và timestamp, nhưng giữ nguyên slug
                    if (this.selectedConversation) {
                        const updatedConv = uniqueConversations.find(
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
                this.isFetchingConversations = false;
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
                    const isFirstLoad = this.isInitialLoad;

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

                    // Lần đầu load: scroll xuống cuối ngay lập tức (không animation)
                    if (isFirstLoad) {
                        // Đánh dấu đã load xong lần đầu trước khi scroll
                        this.isInitialLoad = false;
                        // Scroll ngay lập tức sau khi DOM update
                        this.$nextTick(() => {
                            this.scrollToBottomInstant();
                        });
                    } else if (wasAtBottom || hasNewMessages) {
                        // Các lần sau: chỉ scroll nếu đang ở cuối hoặc có tin nhắn mới
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
            // Reset initial load flag khi chọn conversation mới
            this.isInitialLoad = true;
            this.selectedConversation = conversation;
            this.lastMessagesCount = 0; // Reset khi chọn conversation mới
            this.fetchConversationDetails(conversation.slug);

            // Setup realtime listening for this conversation
            this.listenForMessages(conversation.id);
        },

        listenForMessages(conversationId) {
            // Unsubscribe khỏi channels cũ
            this.cleanupEchoChannels();

            // Subscribe vào channel mới
            const channelName = `conversation.${conversationId}`;
            console.log("🔔 Subscribing to channel:", channelName);

            try {
                const channel = echo
                    .private(channelName)
                    .listen(".ConversationMessageSent", (e) => {
                        console.log("📨 New message received:", e);

                        // Thêm message mới vào list (tránh duplicate)
                        const messageExists = this.currentMessages.some(
                            (msg) => msg.id === e.id
                        );
                        if (!messageExists) {
                            this.currentMessages.push({
                                id: e.id,
                                user_id: e.user_id,
                                message: e.message,
                                user_name: e.user_name,
                                user_image: e.user_image,
                                created_at: e.created_at,
                            });

                            // Auto scroll nếu đang ở cuối
                            if (this.isScrolledToBottom()) {
                                this.$nextTick(() => {
                                    this.scrollToBottom();
                                });
                            }

                            // Update conversation trong danh sách conversations ngay lập tức
                            this.updateConversationInList(conversationId, {
                                latest_message: {
                                    message: e.message,
                                },
                                latest_message_time: e.created_at,
                            });
                        }
                    })
                    .error((error) => {
                        console.error("❌ Channel subscription error:", error);
                        // Retry subscription after a delay if authentication fails
                        if (
                            error &&
                            error.message &&
                            error.message.includes("auth")
                        ) {
                            console.log(
                                "🔄 Retrying subscription after authentication error..."
                            );
                            setTimeout(() => {
                                this.listenForMessages(conversationId);
                            }, 2000);
                        }
                    });

                this.echoChannels.push({ name: channelName, channel });
            } catch (error) {
                console.error("❌ Error subscribing to channel:", error);
            }
        },

        cleanupEchoChannels() {
            this.echoChannels.forEach(({ name }) => {
                console.log("🔕 Leaving channel:", name);
                echo.leave(name);
            });
            this.echoChannels = [];
        },

        // Handle Shift+Enter for new line in textarea
        handleShiftEnter(event) {
            // Allow default behavior (new line) when Shift+Enter is pressed
            return true;
        },

        // Auto-resize textarea
        resizeTextarea() {
            this.$nextTick(() => {
                const textarea = this.$el?.querySelector(".seller-chat-input");
                if (textarea) {
                    textarea.style.height = "auto";
                    textarea.style.height =
                        Math.min(textarea.scrollHeight, 100) + "px";
                }
            });
        },

        // Cập nhật conversation trong danh sách khi có tin nhắn mới
        updateConversationInList(conversationId, updates) {
            const index = this.conversations.findIndex(
                (c) => c.id === conversationId
            );
            if (index !== -1) {
                // Tạo array mới để force Vue reactivity
                const updatedConversations = [...this.conversations];

                // Cập nhật conversation
                updatedConversations[index] = {
                    ...updatedConversations[index],
                    ...updates,
                };

                // Di chuyển conversation có tin nhắn mới lên đầu
                const updatedConv = updatedConversations[index];
                updatedConversations.splice(index, 1);
                updatedConversations.unshift(updatedConv);

                // Update conversations và hash
                this.conversations = updatedConversations;
                this.lastConversationsHash =
                    this.createConversationsHash(updatedConversations);

                // Cập nhật selectedConversation nếu đang chọn conversation này
                if (
                    this.selectedConversation &&
                    this.selectedConversation.id === conversationId
                ) {
                    this.selectedConversation = {
                        ...this.selectedConversation,
                        ...updates,
                    };
                }
            } else {
                // Nếu không tìm thấy trong list, fetch lại để đảm bảo sync
                this.fetchConversations(true);
            }
        },
        async sendMessage() {
            if (!this.messageForm.message || !this.messageForm.message.trim())
                return;
            if (!this.messageForm.conversation_id) return;

            this.sending = true;
            const messageText = this.messageForm.message.trim();

            // Optimistic UI Update: Thêm message vào UI ngay lập tức
            const tempMessage = {
                id: Date.now(), // Temporary ID
                user_id: this.currentUser.id,
                message: messageText,
                user_name: this.currentUser.name,
                user_image: this.currentUser.avatar_original,
                created_at: "Vừa xong",
                _sending: true, // Flag để biết đang gửi
            };

            this.currentMessages.push(tempMessage);
            this.messageForm.message = ""; // Clear input ngay

            // Auto scroll
            this.$nextTick(() => {
                this.scrollToBottom();
            });

            try {
                const res = await this.call_api(
                    "post",
                    "user/new-message-query",
                    { ...this.messageForm, message: messageText }
                );

                if (res.data.success) {
                    // Refresh để có message ID thật và sync data
                    if (this.selectedConversation) {
                        await this.fetchConversationDetails(
                            this.selectedConversation.slug,
                            true // Silent vì đã có optimistic update
                        );
                    }

                    // Cập nhật conversation trong danh sách ngay lập tức
                    if (this.messageForm.conversation_id) {
                        this.updateConversationInList(
                            this.messageForm.conversation_id,
                            {
                                latest_message: {
                                    message: messageText,
                                },
                                latest_message_time: "Vừa xong",
                            }
                        );
                    }

                    // Refresh conversations list để sync với server (sau khi đã update optimistic)
                    await this.fetchConversations(true);
                } else {
                    // Nếu lỗi, remove message tạm
                    const index = this.currentMessages.findIndex(
                        (m) => m.id === tempMessage.id
                    );
                    if (index > -1) {
                        this.currentMessages.splice(index, 1);
                    }

                    this.snack({
                        message:
                            res.data.message ||
                            this.$i18n.t("something_went_wrong"),
                        color: "red",
                    });

                    // Restore message vào input
                    this.messageForm.message = messageText;
                }
            } catch (error) {
                console.error("Error sending message:", error);

                // Remove message tạm nếu lỗi
                const index = this.currentMessages.findIndex(
                    (m) => m.id === tempMessage.id
                );
                if (index > -1) {
                    this.currentMessages.splice(index, 1);
                }

                this.snack({
                    message: this.$i18n.t("something_went_wrong"),
                    color: "red",
                });

                // Restore message vào input
                this.messageForm.message = messageText;
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

        // Scroll xuống cuối ngay lập tức (không animation) - dùng cho lần đầu load
        scrollToBottomInstant() {
            if (this.$refs.messagesContainer) {
                const container = this.$refs.messagesContainer;

                // Tắt scroll-behavior smooth tạm thời
                const originalScrollBehavior = container.style.scrollBehavior;
                container.style.scrollBehavior = "auto";

                // Scroll ngay lập tức (không animation)
                container.scrollTop = container.scrollHeight;

                // Sử dụng double RAF để đảm bảo scroll sau khi DOM render xong
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (this.$refs.messagesContainer) {
                            this.$refs.messagesContainer.scrollTop =
                                this.$refs.messagesContainer.scrollHeight;

                            // Khôi phục scroll-behavior về giá trị ban đầu sau khi scroll xong
                            setTimeout(() => {
                                if (this.$refs.messagesContainer) {
                                    if (originalScrollBehavior) {
                                        this.$refs.messagesContainer.style.scrollBehavior =
                                            originalScrollBehavior;
                                    } else {
                                        this.$refs.messagesContainer.style.scrollBehavior =
                                            "";
                                    }
                                }
                            }, 100);
                        }
                    });
                });
            }
        },
        // Interval đã được xóa - sẽ dùng WebSocket sau này để auto-refresh
    },
    created() {
        if (this.isAuthenticated && this.chatWindowOpen) {
            this.fetchConversations();
        }
        
        // Listen for conversation created event
        window.addEventListener('conversation-created', this.handleConversationCreated);
    },
    beforeUnmount() {
        // Cleanup Echo channels when component is destroyed
        this.cleanupEchoChannels();
        // Remove event listener
        window.removeEventListener('conversation-created', this.handleConversationCreated);
        // Clear timeout nếu có
        if (this.conversationCreatedTimeout) {
            clearTimeout(this.conversationCreatedTimeout);
        }
    },
};
</script>

<style scoped>
.seller-chat-button {
    z-index: 6;
    position: fixed;
    right: 50px;
    bottom: 0;
    border-radius: 8px 8px 0 0;
    overflow: hidden;
    box-shadow: 0 0px 20px rgba(0, 0, 0, 0.16);
    padding: 10px 20px;
    min-width: auto;
    height: auto;
    white-space: nowrap;
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
    min-height: 100%;
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
    height: calc(100% - 20px);
    min-height: 270px;
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
    padding: 12px 16px;
    background: #fafafa;
    border-top: 1px solid #e0e0e0;
    flex-shrink: 0;
    z-index: 10;
}

.seller-chat-input-form {
    width: 100%;
}

.seller-chat-input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 50px;
    padding: 8px 12px;
    transition: border-color 0.2s;
}

.seller-chat-input-wrapper:focus-within {
    border-color: var(--primary, #1976d2);
}

.seller-chat-input {
    flex: 1;
    border: none;
    outline: none;
    resize: none;
    font-size: 14px;
    line-height: 2;
    max-height: 100px;
    min-height: 20px;
    padding: 4px 10px;
    font-family: inherit;
    background: transparent;
    overflow-y: auto;
}

.seller-chat-input::placeholder {
    color: #9e9e9e;
}

.seller-chat-send-btn {
    background: var(--primary, #1976d2);
    color: white;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.1s;
    flex-shrink: 0;
    padding: 0;
}

.seller-chat-send-btn:hover:not(:disabled) {
    background: var(--primary-dark, #1565c0);
    transform: scale(1.05);
}

.seller-chat-send-btn:active:not(:disabled) {
    transform: scale(0.95);
}

.seller-chat-send-btn:disabled {
    background: #e0e0e0;
    color: #bdbdbd;
    cursor: not-allowed;
}

.seller-chat-send-btn i {
    font-size: 18px;
}

.seller-chat-send-loading {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.seller-chat-empty {
    min-height: 0;
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
        bottom: 62px;
        top: auto;
        height: 620px !important;
        max-height: calc(100vh - 32px - 100px) !important;
        border-radius: 0;
    }

    .v-locale--is-rtl .seller-chat-window {
        right: 0 !important;
        left: 0 !important;
    }

    .seller-chat-content {
        position: relative;
        width: 100%;
        height: 100%;
    }

    .seller-chat-list-panel {
        width: 100% !important;
        border-right: none !important;
        position: relative;
    }

    .seller-chat-list-panel.d-none-mobile {
        display: none !important;
    }

    .seller-chat-detail-panel {
        display: none !important;
        width: 100% !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 10;
    }

    .seller-chat-detail-panel.active {
        display: flex !important;
    }

    .seller-chat-messages {
        min-height: 0 !important;
        max-height: none !important;
    }

    .seller-chat-empty {
        min-height: 0 !important;
    }

    .seller-chat-detail-panel {
        overflow: visible !important;
    }

    .seller-chat-input-area {
        display: block !important;
        position: relative !important;
        flex-shrink: 0 !important;
    }

    .btn-back-list {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        min-width: 40px;
    }

    .btn-back-list:hover {
        opacity: 0.7;
    }
}
</style>
