<x-filament::page>
    <style>
        .chat-list {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            margin-bottom: 15px;
        }

        .active {
            background: #2563eb;
            color: #fff !important;
        }

        .chat-list a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            padding: 10px;
            background: #f3f4f6;
            border-radius: 8px;
            text-decoration: none;
            color: #1f2937;
            min-width: 80px;
            position: relative;
        }

        .chat-list a:hover {
            background: #e2e8f0;
        }

        .unseen-list {
            position: absolute;
            top: 0;
            right: 5px;
            background: #ef4444;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            font-size: 12px;
            border-radius: 50%;
        }

        .message-right,
        .message-left {
            display: flex;
            margin-bottom: 10px;
        }

        .message-right {
            justify-content: flex-end;
        }

        .message-left {
            justify-content: flex-start;
            gap: 8px;
        }

        .chat-message {
            max-width: 70%;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
        }

        .chat-message-right {
            background: #dbeafe;
        }

        .chat-message {
            background: #f3f4f6;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 200px);
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 15px;
        }

        #chat-form {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
    </style>

    <div class="chat-container bg-white rounded-lg shadow p-4">
        <div class="chat-list">
            @foreach ($users as $user)
                <a href="{{ request()->url() }}?user={{ $user->id }}" class="{{ $receiverId == $user->id ? 'active' : '' }}">
                    <img class="rounded-full w-12 h-12" src="{{ asset($user->avatar ?: 'avatars/profile.png') }}" alt="User Avatar">
                    <span class="text-xs text-gray-800">{{ $user->name }}</span>
                    @if ($user->chats->where('is_seen', false)->count() > 0)
                        <span class="unseen-list">{{ $user->chats->where('is_seen', false)->count() }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if (!$receiverId)
            <h2 class="text-center text-lg font-semibold text-gray-600">Select a user to start chatting</h2>
        @else
            <div id="chat-messages" class="chat-messages border-t">
                <!-- Chat Messages Loaded Dynamically -->
            </div>

            <form id="chat-form">
                <input type="text" id="chat-input" class="flex-grow border rounded-lg px-3 py-2" placeholder="Type a message">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Send</button>
            </form>
        @endif
    </div>
</x-filament::page>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
    const receiverId = '{{ $receiverId }}';

    function fetchMessages() {
        $.get('{{ route("messages.get") }}', { receiver_id: receiverId }, function (response) {
            const chatMessages = $('#chat-messages');
            chatMessages.html('');
            response.forEach(chat => {
                const side = chat.type === 'admin' ? 'right' : 'left';
                appendMessage(side, chat.message);
            });
            chatMessages.scrollTop(chatMessages.prop('scrollHeight'));
        });
    }

    function appendMessage(side, message) {
        const html = side === 'right'
            ? `<div class="message-right"><div class="chat-message chat-message-right">${message}</div></div>`
            : `<div class="message-left"><div class="chat-message">${message}</div></div>`;
        $('#chat-messages').append(html);
    }

    $('#chat-form').submit(function (e) {
        e.preventDefault();
        const message = $('#chat-input').val().trim();
        if (message) {
            $.post('{{ route("messages.store") }}', {
                message, receiver_id: receiverId, is_admin: true, _token: '{{ csrf_token() }}'
            }).done(() => {
                appendMessage('right', message);
                $('#chat-input').val('');
            });
        }
    });

    const pusher = new Pusher('ad012c372ed42153296c', { cluster: 'ap2' });
    const channel = pusher.subscribe(`chatChannel.${receiverId}`);
    channel.bind('chatEvent', function (data) {
        if (!data.is_admin) appendMessage('left', data.chat.message);
        updateUsers(data.users);
    });

    function updateUsers(users) {
        const userList = $('.chat-list');
        userList.html('');
        users.forEach(user => {
            userList.append(`
                <a href="?user=${user.id}" class="${receiverId == user.id ? 'active' : ''}">
                    <img class="rounded-full w-12 h-12" src="${user.avatar}" alt="${user.name}">
                    <span>${user.name}</span>
                    ${user.unseen ? `<span class="unseen-list">${user.unseen}</span>` : ''}
                </a>
            `);
        });
    }

    fetchMessages();
</script>
@endpush
