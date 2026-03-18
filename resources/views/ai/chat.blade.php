<x-layouts.app title="AI Assistant">
    <x-slot:header>AI Assistant</x-slot:header>

    <div x-data="aiChat()" x-init="init()" class="flex gap-4 h-[calc(100vh-8rem)]">
        {{-- Left: File context --}}
        <div class="w-60 shrink-0 hidden lg:block overflow-y-auto">
            <x-card :padding="'none'" class="mb-4">
                <div class="p-3 border-b border-border bg-surface/50">
                    <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Context Files</p>
                </div>
                <div class="p-3 max-h-64 overflow-y-auto">
                    @if($folders->count())
                        @foreach($folders as $folder)
                            <div class="mb-2">
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 rounded px-1.5 py-1">
                                    <input type="checkbox" @change="toggleFolderFiles({{ $folder->id }})" class="rounded border-gray-300 text-accent focus:ring-accent w-3.5 h-3.5">
                                    <span class="text-xs font-medium text-primary flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                        {{ $folder->name }}
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    @endif
                    @foreach($files as $file)
                        <label class="flex items-center gap-2 py-1.5 cursor-pointer hover:bg-gray-50 rounded px-1.5" :class="{ 'bg-amber-50': contextFiles.includes({{ $file->id }}) }">
                            <input type="checkbox" :checked="contextFiles.includes({{ $file->id }})" @change="toggleFile({{ $file->id }})" class="rounded border-gray-300 text-accent focus:ring-accent w-3.5 h-3.5">
                            <span class="text-xs text-secondary truncate flex-1" x-text="truncateText('{{ $file->original_name }}', 25)"></span>
                            <span class="text-[10px] text-gray-400">{{ $file->file_size }}</span>
                        </label>
                    @endforeach
                    @if($files->isEmpty())
                        <p class="text-xs text-secondary text-center py-4">No files available.</p>
                    @endif
                </div>
            </x-card>

            {{-- RAG Toggle --}}
            <x-card :padding="'none'">
                <div class="p-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="useRag" class="rounded border-gray-300 text-accent focus:ring-accent">
                        <span class="text-xs font-medium text-primary">Use Document Search (RAG)</span>
                    </label>
                    <p class="text-[10px] text-secondary mt-1">AI will search selected files for relevant context</p>
                </div>
            </x-card>
        </div>

        {{-- Center: Chat --}}
        <div class="flex-1 flex flex-col bg-white border border-border rounded-lg overflow-hidden shadow-sm">
            {{-- Chat header --}}
            <div class="px-4 py-3 border-b border-border flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-primary">ResearchFlow AI</h3>
                        <p class="text-[10px] text-secondary">Academic research assistant</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="clearChat()" class="p-1.5 text-secondary hover:text-accent hover:bg-gray-100 rounded transition-colors" title="New chat">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gradient-to-b from-white to-gray-50/30" id="chat-messages" x-ref="messages">
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center px-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl flex items-center justify-center mb-4 shadow-sm">
                            <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <h3 class="text-sm font-semibold text-primary mb-1">ResearchFlow AI</h3>
                        <p class="text-xs text-secondary max-w-sm">Ask me about your research, get help with writing, methodology, deadline planning, or document analysis.</p>
                    </div>
                </template>

                <template x-for="msg in messages" :key="msg.id">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'" class="animate-fade-in">
                        <div :class="{
                            'bg-gradient-to-br from-primary to-gray-800 text-white rounded-2xl rounded-br-md': msg.role === 'user',
                            'bg-white border border-border text-primary rounded-2xl rounded-bl-md shadow-sm': msg.role === 'assistant'
                        }" class="max-w-[85%] px-4 py-3">
                            <div class="text-sm leading-relaxed message-content" x-html="renderMessage(msg.content)"></div>
                            <div class="flex items-center gap-2 mt-1.5" :class="msg.role === 'user' ? 'justify-end text-white/60' : 'text-gray-400'">
                                <span class="text-[10px]" x-text="formatTime(msg.created_at)"></span>
                                <template x-if="msg.metadata?.provider">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-black/10" x-text="msg.metadata.provider"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="loading">
                    <div class="flex justify-start">
                        <div class="bg-white border border-border rounded-2xl rounded-bl-md shadow-sm px-4 py-3">
                            <div class="flex gap-1.5 items-center h-5">
                                <div class="w-2 h-2 bg-accent rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-accent rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-accent rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Input --}}
            <div class="border-t border-border bg-white p-4">
                <form @submit.prevent="send()" class="flex gap-3">
                    <div class="flex-1 relative">
                        <input x-model="input" type="text" placeholder="Ask about your research..." class="w-full rounded-xl border border-border bg-surface px-4 py-2.5 pr-24 text-sm focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-all" :disabled="loading" @keydown.ctrl.enter="send()" @keydown.meta.enter="send()">
                        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1">
                            <span class="text-[10px] text-secondary">Ctrl+Enter</span>
                        </div>
                    </div>
                    <button type="submit" :disabled="loading || !input.trim()" :class="loading || !input.trim() ? 'opacity-50 cursor-not-allowed' : 'hover:bg-amber-600'" class="bg-accent text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all flex items-center gap-2">
                        <template x-if="!loading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </template>
                        <template x-if="loading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </template>
                        <span x-text="loading ? 'Sending...' : 'Send'"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- Right: Quick Actions & History --}}
        <div class="w-56 shrink-0 hidden xl:block overflow-y-auto">
            <x-card :padding="'none'" class="mb-4">
                <div class="p-3 border-b border-border bg-surface/50">
                    <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Quick Actions</p>
                </div>
                <div class="p-2 space-y-0.5">
                    <template x-for="action in quickActions" :key="action.label">
                        <button @click="input = action.prompt; send()" class="w-full text-left text-xs text-secondary hover:text-accent hover:bg-amber-50 rounded-lg px-2.5 py-2 transition-colors flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-gradient-to-br from-amber-100 to-orange-100 flex items-center justify-center flex-shrink-0" x-html="action.icon"></span>
                            <span x-text="action.label"></span>
                        </button>
                    </template>
                </div>
            </x-card>

            <x-card :padding="'none'">
                <div class="p-3 border-b border-border bg-surface/50 flex items-center justify-between">
                    <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Conversations</p>
                    <button @click="clearChat()" class="text-[10px] text-accent hover:underline">+ New</button>
                </div>
                <div class="max-h-64 overflow-y-auto p-2">
                    <template x-for="conv in conversations" :key="conv.id">
                        <button @click="loadConversation(conv.id)" class="w-full text-left text-xs text-secondary hover:text-accent hover:bg-gray-50 rounded-lg px-2.5 py-2 transition-colors truncate" :class="{ 'bg-amber-50 text-accent': currentConversation === conv.id }" x-text="conv.title"></button>
                    </template>
                    <template x-if="conversations.length === 0">
                        <p class="text-xs text-secondary text-center py-4">No conversations yet</p>
                    </template>
                </div>
            </x-card>
        </div>
    </div>

    @push('styles')
    <style>
        .message-content h1, .message-content h2, .message-content h3 { margin-top: 1em; margin-bottom: 0.5em; font-weight: 600; }
        .message-content h1 { font-size: 1.25rem; }
        .message-content h2 { font-size: 1.1rem; }
        .message-content h3 { font-size: 1rem; }
        .message-content p { margin-bottom: 0.75em; }
        .message-content ul, .message-content ol { margin-left: 1.5em; margin-bottom: 0.75em; }
        .message-content li { margin-bottom: 0.25em; }
        .message-content code { background: rgba(0,0,0,0.05); padding: 0.125em 0.375em; border-radius: 0.25rem; font-size: 0.875em; }
        .message-content pre { background: #1f2937; color: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 0.75em 0; }
        .message-content pre code { background: transparent; padding: 0; color: inherit; }
        .message-content blockquote { border-left: 3px solid #d97706; padding-left: 1em; margin: 0.75em 0; color: #6b7280; font-style: italic; }
        .message-content a { color: #d97706; text-decoration: underline; }
        .message-content strong { font-weight: 600; }
        .message-content em { font-style: italic; }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @endpush

    @push('scripts')
    <script>
        function aiChat() {
            return {
                input: '',
                messages: [],
                conversations: [],
                currentConversation: null,
                contextFiles: [],
                useRag: false,
                loading: false,

                quickActions: [
                    { label: 'Summarize latest report', prompt: 'Summarize my latest progress report and highlight key achievements and challenges.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
                    { label: 'Check deadline risks', prompt: 'What tasks are at risk of missing their deadline? Please analyze and prioritize.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                    { label: 'Suggest next tasks', prompt: 'Based on my current progress, what should be my next 3-5 tasks?', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>' },
                    { label: 'Methodology review', prompt: 'Review my research methodology and suggest improvements.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>' },
                    { label: 'Writing assistance', prompt: 'Help me improve my academic writing style and clarity.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' },
                ],

                async init() {
                    try {
                        const res = await axios.get('/api/ai/conversations');
                        this.conversations = res.data;
                    } catch(e) {
                        console.error('Failed to load conversations:', e);
                    }
                },

                toggleFile(id) {
                    const idx = this.contextFiles.indexOf(id);
                    idx > -1 ? this.contextFiles.splice(idx, 1) : this.contextFiles.push(id);
                },

                toggleFolderFiles(folderId) {
                    // In a real implementation, you'd fetch files in this folder
                    const folderFiles = [1, 2, 3]; // Placeholder
                    folderFiles.forEach(id => this.toggleFile(id));
                },

                async clearChat() {
                    this.messages = [];
                    this.currentConversation = null;
                    this.input = '';
                },

                async loadConversation(id) {
                    try {
                        const res = await axios.get(`/api/ai/conversations/${id}/messages`);
                        this.messages = res.data;
                        this.currentConversation = id;
                        this.$nextTick(() => this.scrollToBottom());
                    } catch(e) {
                        console.error('Failed to load conversation:', e);
                    }
                },

                async send() {
                    if (!this.input.trim() || this.loading) return;
                    const content = this.input;
                    this.input = '';

                    // Create conversation if needed
                    if (!this.currentConversation) {
                        try {
                            const res = await axios.post('/api/ai/conversations', {
                                title: content.substring(0, 50) + (content.length > 50 ? '...' : ''),
                                student_id: {{ $student?->id ?? 'null' }},
                                context_files: this.contextFiles,
                                scope: '{{ $student ? "student" : "general" }}'
                            });
                            this.currentConversation = res.data.id;
                            this.conversations.unshift(res.data);
                        } catch(e) {
                            console.error('Failed to create conversation:', e);
                            return;
                        }
                    }

                    // Add user message optimistically
                    const userMsg = {
                        id: Date.now(),
                        role: 'user',
                        content: content,
                        created_at: new Date().toISOString()
                    };
                    this.messages.push(userMsg);
                    this.loading = true;
                    this.scrollToBottom();

                    try {
                        const res = await axios.post(`/api/ai/conversations/${this.currentConversation}/messages`, {
                            content: content,
                            use_rag: this.useRag
                        });
                        this.messages = res.data.conversation.messages;
                    } catch(e) {
                        this.messages.push({
                            id: Date.now() + 1,
                            role: 'assistant',
                            content: 'Sorry, something went wrong. Please try again.',
                            created_at: new Date().toISOString()
                        });
                    }

                    this.loading = false;
                    this.scrollToBottom();
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const el = this.$refs.messages;
                        if (el) el.scrollTop = el.scrollHeight;
                    });
                },

                renderMessage(text) {
                    // Basic markdown rendering
                    let html = text
                        // Escape HTML
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        // Code blocks
                        .replace(/```(\w+)?\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>')
                        // Inline code
                        .replace(/`([^`]+)`/g, '<code>$1</code>')
                        // Bold
                        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                        // Italic
                        .replace(/\*([^*]+)\*/g, '<em>$1</em>')
                        // Headers
                        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
                        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
                        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
                        // Lists
                        .replace(/^\- (.+)$/gm, '<li>$1</li>')
                        .replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>')
                        // Line breaks
                        .replace(/\n\n/g, '</p><p>')
                        .replace(/\n/g, '<br>');

                    return '<p>' + html + '</p>';
                },

                formatTime(isoString) {
                    if (!isoString) return '';
                    const date = new Date(isoString);
                    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                },

                truncateText(text, maxLength) {
                    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
