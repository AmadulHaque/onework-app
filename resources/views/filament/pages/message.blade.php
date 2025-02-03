<x-filament::page>
    <style>
        .chat-list {
            display: block ruby;
            gap: 10px;
            overflow-x: auto;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            margin-bottom: auto;
            justify-content: center;
            padding-bottom: 25px;
            margin-bottom: 10px;
        }

        .active {
            background: #dc8624 !important;
            color: #fff !important;
        }
        .chat-list a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: #f1f5f9;
            border-radius: 8px;
            text-decoration: none;
            color: #1f2937;
            position: relative;
        }

        .chat-list a:hover {
            background: #e2e8f0;
        }
        .unseen-list {
            position: absolute;
            background: white;
            width: 25px;
            border-radius: 50px;
            left: 0;
            top: -5px;
            z-index: 111111;
            color: red;
            font-weight: bold;
        }
        .message-right,
        .message-left {
            width: 100%;
            display: flex;
            margin-bottom: 8px;
        }

        .message-right {
            justify-content: flex-end;
        }

        .message-left {
            justify-content: flex-start;
            align-items: center;
            gap: 8px;
        }

        .chat-message {
            max-width: 70%;
            padding: 10px;
            border-radius: 8px;
            background: #f3f4f6;
        }

        .chat-message-right {
            background: #d1fae5;
        }

        .chat-container {
            text-align: center;
        }
    </style>
    @php
        $receiverId = request()->input('user') ?? null;
        $users = \App\Models\User::where('id', '!=', auth()->user()->id)->with('chats')->latest('updated_at')->limit(10)->get();
        if ($receiverId) {
            \App\Models\Chat::where('sender_id', $receiverId)
                ->update([
                    'is_seen' => true
                ]);

        }
    @endphp
    <div class="chat-container bg-white rounded-lg shadow-lg p-6">
        <div id="users" class="chat-list">
            @foreach ($users as $user)
            <a href="{{ request()->url() }}?user={{ $user->id }}" class="{{ $receiverId == $user->id ? 'active' : ''  }}">
                <img class="rounded-full w-10 h-10" src="{{ asset($user->avatar ?  $user->avatar : 'avatars/profile.png') }}" alt="Chat 1">
                <span>{{ $user->name }} </span>
                @if ($user->chats->where('is_seen', false)->count() > 0)
                <span class="unseen-list">{{  $user->chats->where('is_seen', false)->count() }}</span>
                @endif
            </a>
            @endforeach
        </div>

        @if (!$receiverId)
            <h2 class="text-center text-xl font-semibold text-black dark:text-gray-800">Please select a user to chat with</h2>
        @else
            <div id="chat-messages" class="h-96 overflow-y-auto border-b border-gray-300 mb-4 p-4">
                <!-- Dynamic Messages Will Appear Here -->
            </div>

            <form id="chat-form" class="flex mt-4" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="is_admin" id="" value="1">
                <input type="hidden" name="receiver_id" id="" value="{{ $receiverId }}">
                <div class="fi-input-wrp-input min-w-0 flex-1">
                    <input type="file" name="file" id="file" class="flex-grow border rounded-lg p-2 mr-2 " />
                </div>
                <input type="text" name="message" id="chat-input" class="flex-grow border rounded-lg p-2 mr-2" style="margin-right: 3px;" placeholder="Type your message..." />
                <x-filament::button  type="submit" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Send
                </x-filament::button>
            </form>
        @endif
    </div>
</x-filament::page>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" crossorigin="anonymous"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<script>
    const receiverId = '{{ $receiverId }}';

    // Fetch existing chat messages
    function fetchMessages() {
        $.ajax({
            url: '{{ route("messages.get") }}',
            type: 'GET',
            data: {
                receiver_id: receiverId,
            },
            success: function (response) {
                const chatMessages = $('#chat-messages');
                chatMessages.html('');
                response.forEach(chat => {
                    const side = chat.is_admin ? 'right' : 'left';
                    appendMessage(side, chat.message, chat.sender_id !== '{{ auth()->user()->id }}' ? '{{ asset('avatars/profile.png') }}' : null,chat.file);
                });
                chatMessages.scrollTop(chatMessages.prop('scrollHeight'));
            },
            error: function (error) {
                console.error('Failed to fetch messages:', error);
            }
        });
    }

    // Append message to the chat UI
    function appendMessage(side, message, avatar = null, file = null) {

        const chatMessages = $('#chat-messages');
        const messageHtml = side === 'right' ? `
            ${file ? `<div class="message-right mt-2">
               <img class="" style="width: 80px; height: 80px;" src="${file}" alt="File">
            </div>` : ''}
            <div class="message-right">
                <div class="chat-message chat-message-right dark:text-gray-800">${message}</div>
            </div>
        ` : `
            ${file ? `<div class="message-left mt-2">
               <img class="" style="width: 80px; height: 80px;" src="${file}" alt="File">
            </div>` : ''}
            <div class="message-left">
                <img class="rounded-full w-10 h-10" src="${avatar}" alt="Avatar">
                <div class="chat-message dark:text-gray-800">${message}</div>
            </div>
        `;
        chatMessages.append(messageHtml);
    }

    // Handle sending a message
    $('#chat-form').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const message = $('#chat-input').val().trim();

        if (message) {
            $.ajax({
                url: '{{ route("messages.store") }}',
                type: 'POST',
                data: formData,
                processData: false,  // Prevent jQuery from processing the data
                contentType: false,  // Prevent jQuery from setting the content type
                success: function (response) {
                    // console.log(response);

                    appendMessage('right', message, '', response.file);
                    // Reset form fields
                    $('#chat-input').val('');
                    $('#file').val('');
                },
                error: function (error) {
                    console.error('Message sending failed:', error);
                }
            });
        }
    });


    // Initialize Pusher for real-time updates
    const pusher = new Pusher('ad012c372ed42153296c', { cluster: 'ap2' });
    const channel = pusher.subscribe(`chatChannel.${receiverId}`);
    channel.bind('chatEvent', function (chat) {
       if (!chat.is_admin) {
           appendMessage('left', chat.message,'{{ asset('avatars/profile.png') }}',chat.file);
       }
    });

    const channel2 = pusher.subscribe(`chatChannel.admin123`);
    channel2.bind('chatEvent', function (users) {
        getLatestUsers(users);
    });

    fetchMessages();
    // Fetch latest users and show
    function getLatestUsers(users) {
        $('#users').html('');
        users.map((user) => {
           $('#users').append(`<a href="?user=${user.id }" class="${receiverId == user.id ? 'active' : ''}" style="margin-right: 6px;">
                                <img class="rounded-full w-10 h-10" src="${user.avatar}" alt="${user.name}">
                                <span> ${user.name}</span>
                                ${user.unseen > 0 ? `<span class="unseen-list">${user.unseen}</span>` : ''}
                            </a>`);
        })
    }

</script>


@endpush
