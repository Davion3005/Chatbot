<!DOCTYPE html>
<html>
<head>
    <title>AI Chatbot</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-3xl mx-auto mt-10" x-data='chatbot(@json($conversation))'>

    <div class="bg-white shadow rounded-lg p-4 h-[600px] flex flex-col">

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto space-y-3">

            <template x-for="message in messages">

                <div :class="message.role === 'user' ? 'text-right' : 'text-left'">

                    <div
                        class="inline-block px-4 py-2 rounded-lg"
                        :class="message.role === 'user'
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-200 text-black'"
                        x-text="message.content"
                    ></div>

                </div>

            </template>

        </div>

        <!-- Input -->
        <div class="mt-4 flex">

            <input
                type="text"
                x-model="input"
                @keydown.enter="sendMessage"
                class="flex-1 border rounded px-3 py-2"
                placeholder="Ask something..."
            >

            <button
                @click="sendMessage"
                class="ml-2 bg-blue-500 text-white px-4 py-2 rounded"
            >
                Send
            </button>

        </div>

    </div>

</div>

<script>

    function chatbot(conversation) {
        return {
            messages: conversation?.messages || [],
            input: '',

            async sendMessage() {

                if (!this.input) return;

                let message = this.input;

                this.messages.push({
                    role: 'user',
                    content: message
                });

                this.input = '';

                const response = await fetch('/bot/chat/' + conversation.id, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ message })
                });

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                this.messages.push({ role: 'assistant', content: '' });
                // Use index to mutate through Alpine's reactive proxy so the DOM updates
                const msgIdx = this.messages.length - 1;

                let buffer = '';

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });

                    const lines = buffer.split('\n');
                    buffer = lines.pop(); // keep incomplete tail for next iteration

                    for (const line of lines) {
                        if (!line.trim()) continue;

                        try {
                            const data = JSON.parse(line);
                            this.messages[msgIdx].content += data.response ?? '';
                        } catch (e) {
                            // skip malformed lines
                        }
                    }
                }
            }

        }

    }

</script>

</body>
</html>
