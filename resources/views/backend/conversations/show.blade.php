@extends('backend.layouts.app')

@section('content')
  <div class="aiz-titlebar mt-2 mb-4">
        <div class="h6">
            <span>{{ translate('Query With')}} </span>
            @if ($conversation->sender_id == Auth::user()->id && $conversation->receiver->shop != null)
                <a href="{{ route('shop.visit', $conversation->receiver->shop->slug) }}" class="">{{ $conversation->receiver->shop->name }}</a>
                @else
                    {{ $conversation->sender->name }}
            @endif
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title fs-16 fw-600 mb-0">#{{ $conversation->title }}
            (
                {{ translate('Between you and') }}
                @if ($conversation->sender_id == Auth::user()->id)
                    {{ $conversation->receiver->name }}
                @else
                    {{ $conversation->sender->name }}
                @endif
            )
            </h5>
        </div>

        <div class="card-body">
            <ul class="list-group list-group-flush" id="messages">
                @foreach($conversation->messages as $message)
                    <li class="list-group-item px-0">
                        <div class="media mb-2">
                          <img class="avatar avatar-xs mr-3" @if($message->user != null) src="{{ uploaded_asset($message->user->avatar) }}" @endif onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                          <div class="media-body">
                            <h6 class="mb-0 fw-600">
                                @if ($message->user != null)
                                    {{ $message->user->name }}
                                @endif
                            </h6>
                            <p class="opacity-50">{{$message->created_at}}</p>
                          </div>
                        </div>
                        <p>
                            {{ $message->message }}
                        </p>
                    </li>
                @endforeach
            </ul>
            <form class="pt-4" id="message-form" onsubmit="return false;">
                @csrf
                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                <div class="form-group">
                    <textarea class="form-control" rows="4" name="message" id="message-input" placeholder="{{ translate('Type your reply') }}" required></textarea>
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="button" class="btn btn-primary" id="send-btn" onclick="sendMessage()">{{ translate('Send') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <!-- Pusher JS -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    
    <script type="text/javascript">
    // Initialize Pusher Echo
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: '{{ config('broadcasting.connections.pusher.key') }}',
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        forceTLS: true,
        encrypted: true,
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    fetch('/broadcasting/auth', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            socket_id: socketId,
                            channel_name: channel.name
                        })
                    })
                    .then(response => response.json())
                    .then(data => callback(null, data))
                    .catch(error => callback(error));
                }
            };
        },
    });

    // Listen for new messages
    console.log('🔔 Subscribing to channel: conversation.{{ $conversation->id }}');
    Echo.private('conversation.{{ $conversation->id }}')
        .listen('.ConversationMessageSent', (e) => {
            console.log('📨 New message received:', e);
            
            // Add new message to UI
            addMessageToUI(e);
            
            // Scroll to bottom
            scrollToBottom();
        });

    function addMessageToUI(message) {
        var html = `
            <li class="list-group-item px-0">
                <div class="media mb-2">
                    <img class="avatar avatar-xs mr-3" src="${message.user_image || '{{ static_asset('assets/img/avatar-place.png') }}'}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';">
                    <div class="media-body">
                        <h6 class="mb-0 fw-600">${message.user_name}</h6>
                        <p class="opacity-50">${message.created_at}</p>
                    </div>
                </div>
                <p>${message.message}</p>
            </li>
        `;
        $('#messages').append(html);
    }

    function scrollToBottom() {
        var messagesContainer = document.getElementById('messages');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    // Send message via AJAX
    function sendMessage() {
        var messageInput = $('#message-input');
        var message = messageInput.val().trim();
        
        if (!message) return;
        
        var sendBtn = $('#send-btn');
        sendBtn.prop('disabled', true).text('{{ translate('Sending...') }}');
        
        // Add message to UI immediately (optimistic update)
        addMessageToUI({
            user_name: '{{ Auth::user()->name }}',
            user_image: '{{ uploaded_asset(Auth::user()->avatar) }}',
            message: message,
            created_at: '{{ translate('Just now') }}'
        });
        
        messageInput.val('');
        scrollToBottom();
        
        // Send via AJAX
        $.post('{{ route('querries.store') }}', {
            _token: '{{ csrf_token() }}',
            conversation_id: '{{ $conversation->id }}',
            message: message
        })
        .done(function(response) {
            console.log('✅ Message sent successfully');
        })
        .fail(function(xhr, status, error) {
            console.error('❌ Error sending message:', error);
            AIZ.plugins.notify('danger', '{{ translate('Failed to send message') }}');
        })
        .always(function() {
            sendBtn.prop('disabled', false).text('{{ translate('Send') }}');
        });
    }

    // Allow Enter to send (Shift+Enter for new line)
    $('#message-input').on('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Scroll to bottom on page load
    $(document).ready(function() {
        scrollToBottom();
    });
    </script>
@endsection