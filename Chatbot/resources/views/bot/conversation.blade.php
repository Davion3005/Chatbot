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
                        class="inline-block px-4 py-2 rounded-lg max-w-[80%] text-left"
                        :class="message.role === 'user'
                            ? 'bg-blue-500 text-white'
                            : 'bg-gray-200 text-black'"
                    >
                        <!-- Attachment chips inside bubble -->
                        <template x-if="message.attachments && message.attachments.length > 0">
                            <div class="flex flex-wrap gap-1 mb-1">
                                <template x-for="(att, i) in message.attachments" :key="i">
                                    <span class="flex items-center gap-1 bg-white/20 text-xs px-2 py-0.5 rounded-full">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828A4 4 0 1012.343 4.1L5.757 10.686a6 6 0 108.486 8.485L20.5 13" />
                                        </svg>
                                        <span x-text="att.name" class="max-w-[120px] truncate"></span>
                                    </span>
                                </template>
                            </div>
                        </template>

                        <span x-text="message.content"></span>
                    </div>

                </div>

            </template>

        </div>

        <!-- Input -->
        <div class="mt-4 flex flex-col gap-2">

            <!-- File preview chips -->
            <div x-show="files.length > 0" class="flex flex-wrap gap-2">
                <template x-for="(file, index) in files" :key="index">
                    <div class="flex items-center gap-1 bg-blue-50 border border-blue-200 text-blue-700 text-sm px-2 py-1 rounded-full">

                        <!-- File type icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828A4 4 0 1012.343 4.1L5.757 10.686a6 6 0 108.486 8.485L20.5 13" />
                        </svg>

                        <span class="max-w-[160px] truncate" x-text="file.name"></span>
                        <span class="text-blue-400 text-xs" x-text="formatFileSize(file.size)"></span>

                        <button @click="removeFile(index)" class="ml-1 text-blue-400 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex items-center gap-2">

                <!-- Hidden file input -->
                <input
                    type="file"
                    x-ref="fileInput"
                    @change="onFileChange"
                    multiple
                    class="hidden"
                    accept="image/*,.pdf,.doc,.docx,.txt,.csv,.xlsx,.xls"
                >

                <!-- Attach button -->
                <button
                    @click="$refs.fileInput.click()"
                    title="Attach files"
                    class="p-2 text-gray-400 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-200"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828A4 4 0 1012.343 4.1L5.757 10.686a6 6 0 108.486 8.485L20.5 13" />
                    </svg>
                </button>

                <input
                    type="text"
                    x-model="input"
                    @keydown.enter="sendMessage"
                    class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-300"
                    placeholder="Ask something..."
                >

                <button
                    @click="sendMessage"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                >
                    Send
                </button>

            </div>

        </div>

    </div>

</div>

<script>

    function chatbot(conversation) {
        return {
            messages: conversation?.messages || [],
            input: '',
            files: [],

            onFileChange(event) {
                const selected = Array.from(event.target.files);
                // Merge, avoid duplicates by name+size
                selected.forEach(f => {
                    if (!this.files.find(e => e.name === f.name && e.size === f.size)) {
                        this.files.push(f);
                    }
                });
                // Reset input so the same file can be re-added after removal
                event.target.value = '';
            },

            removeFile(index) {
                this.files.splice(index, 1);
            },

            formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            },

            async sendMessage() {
                if (!this.input && this.files.length === 0) return;
                let message = this.input;
                let attachments = [...this.files];

                this.messages.push({
                    role: 'user',
                    content: message,
                    attachments: attachments.map(f => ({ name: f.name, size: f.size }))
                });

                this.input = '';
                this.files = [];

                const formData = new FormData();
                formData.append('message', message);
                attachments.forEach(f => formData.append('files[]', f));

                const response = await fetch('/bot/chat/' + conversation.id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        // Note: do NOT set Content-Type — browser sets it with boundary for FormData
                    },
                    body: formData
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
