<!DOCTYPE html>
<html>
<head>
    <title>Chat UI Index Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-200 min-h-screen flex items-center justify-center">

<div class="w-full max-w-3xl">

    <div class="bg-white rounded-xl shadow p-4 h-[600px] flex flex-col">

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto mb-4 space-y-2">
            <div class="text-gray-400 text-center mt-10">
                Start a conversation...
            </div>

        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('bot.createConversation') }}" class="flex">
            @csrf

            <input
                type="text"
                name="initial_message"
                value="{{ old('initial_message') }}"
                placeholder="Type a message..."
                class="flex-1 border rounded-lg px-3 py-2 focus:outline-none"
            >

            <button
                type="submit"
                class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg"
            >
                Send
            </button>
        </form>

    </div>

</div>

</body>
</html>
