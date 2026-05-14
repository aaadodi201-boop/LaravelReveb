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

<script>
document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('chat-form');
    const messageInput = document.getElementById('message');
    const chatBox = document.getElementById('chat-box');

    function appendMessage(user, message) {
        chatBox.innerHTML += `
            <div class="mb-2">
                <strong>${user}:</strong> ${message}
            </div>
        `;
        chatBox.scrollTop = chatBox.scrollHeight;
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
    }

});
</script>

</body>
</html>