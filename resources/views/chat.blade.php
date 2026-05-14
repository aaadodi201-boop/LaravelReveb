<!DOCTYPE html>
<html>
<head>
    <title>Mini Chat</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="p-5">

<h1 class="text-2xl font-bold mb-4">
    Mini Chat Real-time
</h1>

<div class="flex gap-5">
    <div class="flex-1">
        <div id="chat-box"
             class="border rounded p-4 h-96 overflow-y-scroll mb-4">

            @foreach($messages as $msg)
                <div class="mb-2">
                    <strong>{{ $msg->user->name }}:</strong>
                    {{ $msg->message }}
                </div>
            @endforeach

        </div>

        <form id="chat-form" class="flex gap-2">

            @csrf

            <input
                type="text"
                id="message"
                class="border p-2 flex-1 rounded"
                placeholder="Ketik pesan..."
            >

            <button
                class="bg-blue-500 text-white px-4 py-2 rounded">

                Kirim

            </button>

        </form>
    </div>

    <div class="w-64">
        <div class="border rounded p-4 bg-gray-50">
            <h2 class="text-lg font-bold mb-3">User Online</h2>
            <div id="online-users" class="space-y-2">
                <div class="text-gray-500 text-sm">Memuat...</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const chatBox = document.getElementById('chat-box');
    const onlineUsersList = document.getElementById('online-users');

    function appendMessage(user, message) {
        chatBox.innerHTML += `
            <div class="mb-2">
                <strong>${user}:</strong> ${message}
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function updateOnlineUsers(users) {
        if (users.length === 0) {
            onlineUsersList.innerHTML = '<div class="text-gray-500 text-sm">Tidak ada user online</div>';
        } else {
            onlineUsersList.innerHTML = users.map(user => `
                <div class="flex items-center gap-2 p-2 bg-white rounded border border-gray-200">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-sm">${user.name}</span>
                </div>
            `).join('');
        }
    }

    // kirim pesan saja, tidak render di sini
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const message = messageInput.value;

        await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ message })
        });

        messageInput.value = '';
    });

    // render hanya dari Reverb
    if (window.Echo) {
        window.Echo.channel('chat')
            .listen('.MessageSent', (e) => {
                appendMessage(
                    e.message.user.name,
                    e.message.message
                );
            });

        // Listen to presence channel
        window.Echo.join('chat.online')
            .here((users) => {
                updateOnlineUsers(users);
            })
            .joining((user) => {
                console.log(user.name + ' joined');
            })
            .leaving((user) => {
                console.log(user.name + ' left');
            })
            .error((error) => {
                console.error('Error:', error);
            });
    }

});
</script>

</body>
</html>