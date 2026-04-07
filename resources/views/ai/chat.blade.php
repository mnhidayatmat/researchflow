<x-layouts.app title="AI Assistant">
    <x-slot:header>AI Assistant</x-slot:header>

    <div
        x-data="aiChat()"
        x-init="init()"
        class="flex h-[calc(100vh-7rem)] lg:h-[calc(100vh-7rem)] overflow-hidden rounded-2xl border border-border dark:border-dark-border bg-card dark:bg-dark-card shadow-sm relative"
        style="max-height: calc(100dvh - 7rem);"
    >

        {{-- ===== MOBILE SIDEBAR BACKDROP ===== --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
        ></div>

        {{-- ===== LEFT SIDEBAR ===== --}}
        <div
            x-cloak
            class="fixed inset-y-0 left-0 z-50 w-[280px] max-w-[85vw] shrink-0 flex flex-col border-r border-border dark:border-dark-border bg-card dark:bg-dark-card lg:static lg:z-auto lg:w-72 lg:max-w-none lg:translate-x-0 transition-transform duration-200 ease-out"
            :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'"
        >
            {{-- Sidebar Header --}}
            <div class="flex items-center justify-between px-4 py-4 border-b border-border dark:border-dark-border shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-accent to-amber-600 flex items-center justify-center shadow-sm">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-primary dark:text-dark-primary">ResearchFlow AI</span>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="newChat(); sidebarOpen = false"
                        title="New chat"
                        class="p-2 rounded-lg text-secondary dark:text-dark-secondary hover:bg-surface dark:hover:bg-dark-surface hover:text-primary dark:hover:text-dark-primary transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                    <button
                        @click="sidebarOpen = false"
                        class="p-2 rounded-lg text-secondary dark:text-dark-secondary hover:bg-surface dark:hover:bg-dark-surface lg:hidden transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Search conversations --}}
            <div class="px-3 py-2.5 shrink-0">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-tertiary dark:text-dark-tertiary pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        x-model="searchQuery"
                        placeholder="Search chats..."
                        class="w-full pl-8 pr-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary focus:border-accent dark:focus:border-dark-accent focus:ring-1 focus:ring-accent/20 outline-none transition-colors"
                    >
                </div>
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto scrollbar-thin py-1 space-y-0.5 px-2">
                <template x-if="filteredConversations().length === 0 && conversations.length === 0">
                    <div class="text-center py-12 px-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                        </div>
                        <p class="text-xs font-medium text-secondary dark:text-dark-secondary">No conversations yet</p>
                        <p class="text-[10px] text-tertiary dark:text-dark-tertiary mt-1">Start a new chat to begin</p>
                    </div>
                </template>

                <template x-if="filteredConversations().length === 0 && conversations.length > 0 && searchQuery.trim()">
                    <div class="text-center py-8 px-4">
                        <p class="text-xs text-tertiary dark:text-dark-tertiary">No chats matching your search</p>
                    </div>
                </template>

                <template x-for="(group, label) in groupedFilteredConversations()" :key="label">
                    <div>
                        <p class="px-2 pt-4 pb-1.5 text-[10px] font-semibold uppercase tracking-widest text-tertiary dark:text-dark-tertiary" x-text="label"></p>
                        <template x-for="conv in group" :key="conv.id">
                            <div
                                class="group flex items-center gap-2 rounded-xl px-3 py-2.5 cursor-pointer transition-all duration-150"
                                :class="currentConversationId === conv.id
                                    ? 'bg-accent/10 dark:bg-dark-accent/10 shadow-sm'
                                    : 'hover:bg-surface dark:hover:bg-dark-surface'"
                                @click="loadConversation(conv.id); if(window.innerWidth < 1024) sidebarOpen = false"
                            >
                                <svg class="w-4 h-4 shrink-0" :class="currentConversationId === conv.id ? 'text-accent dark:text-dark-accent' : 'text-tertiary dark:text-dark-tertiary'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span
                                    class="flex-1 truncate text-xs leading-snug"
                                    :class="currentConversationId === conv.id ? 'text-accent dark:text-dark-accent font-semibold' : 'text-primary dark:text-dark-primary'"
                                    x-text="conv.title"
                                ></span>
                                <button
                                    type="button"
                                    @click.stop="deleteConversation(conv.id)"
                                    class="opacity-0 group-hover:opacity-100 shrink-0 p-1 rounded-lg text-tertiary dark:text-dark-tertiary hover:text-danger dark:hover:text-dark-danger hover:bg-danger/10 transition-all"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Sidebar Footer --}}
            <div class="px-3 py-3 border-t border-border dark:border-dark-border shrink-0">
                <button
                    @click="newChat(); sidebarOpen = false"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Conversation
                </button>
            </div>
        </div>

        {{-- ===== MAIN CHAT AREA ===== --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Chat Topbar --}}
            <div class="flex items-center justify-between px-3 sm:px-4 py-3 border-b border-border dark:border-dark-border shrink-0">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1">
                    {{-- Mobile sidebar toggle --}}
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-1 rounded-xl text-secondary dark:text-dark-secondary hover:bg-surface dark:hover:bg-dark-surface hover:text-primary dark:hover:text-dark-primary transition-colors shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-medium text-primary dark:text-dark-primary truncate" x-text="currentTitle || 'New Chat'"></h3>
                        <p class="text-[10px] text-tertiary dark:text-dark-tertiary mt-0.5 hidden sm:block" x-show="messages.length > 0" x-text="messages.length + ' message' + (messages.length !== 1 ? 's' : '')"></p>
                    </div>
                </div>
                <button
                    @click="newChat()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface border border-border dark:border-dark-border transition-colors shrink-0"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">New Chat</span>
                </button>
            </div>

            {{-- Messages --}}
            <div
                class="flex-1 overflow-y-auto scrollbar-thin px-3 sm:px-6 py-4 sm:py-6 space-y-4 sm:space-y-6"
                x-ref="messages"
            >
                {{-- Welcome screen --}}
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full text-center max-w-lg mx-auto px-2 sm:px-4">
                        {{-- Animated AI icon --}}
                        <div class="relative mb-6">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-gradient-to-br from-accent/20 to-amber-500/10 flex items-center justify-center">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-success flex items-center justify-center border-2 border-card dark:border-dark-card">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>

                        <h2 class="text-lg sm:text-xl font-semibold text-primary dark:text-dark-primary mb-1.5">How can I help you today?</h2>
                        <p class="text-xs sm:text-sm text-secondary dark:text-dark-secondary mb-6 sm:mb-8 leading-relaxed max-w-sm">Ask about research planning, writing, methodology, or upload a document to analyse.</p>

                        {{-- Quick actions --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-2.5 w-full">
                            <template x-for="action in quickActions" :key="action.label">
                                <button
                                    type="button"
                                    @click="input = action.prompt; $nextTick(() => $refs.inputField.focus())"
                                    class="text-left rounded-xl border border-border dark:border-dark-border bg-card dark:bg-dark-card px-3.5 py-3 hover:border-accent/30 hover:bg-accent/5 dark:hover:bg-dark-accent/10 transition-all group active:scale-[0.98]"
                                >
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-accent/10 flex items-center justify-center shrink-0 mt-0.5 group-hover:bg-accent/20 transition-colors">
                                            <svg class="w-3.5 h-3.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-primary dark:text-dark-primary group-hover:text-accent dark:group-hover:text-dark-accent transition-colors leading-snug" x-text="action.label"></p>
                                            <p class="text-[10px] text-tertiary dark:text-dark-tertiary mt-0.5 leading-relaxed line-clamp-2" x-text="action.hint"></p>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Message bubbles --}}
                <template x-for="msg in messages" :key="msg.id">
                    <div
                        :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start gap-2 sm:gap-3'"
                        class="animate-fade-in"
                    >
                        {{-- AI Avatar --}}
                        <template x-if="msg.role === 'assistant'">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-accent to-amber-600 flex items-center justify-center shrink-0 mt-0.5 shadow-sm">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </template>

                        <div
                            :class="{
                                'max-w-[85%] sm:max-w-[75%] bg-primary dark:bg-dark-primary text-white rounded-2xl rounded-br-md px-3.5 sm:px-4 py-2.5 sm:py-3 shadow-sm': msg.role === 'user',
                                'flex-1 min-w-0 max-w-full sm:max-w-[85%]': msg.role === 'assistant',
                            }"
                        >
                            <div
                                class="text-[13px] sm:text-sm leading-relaxed break-words"
                                :class="msg.role === 'assistant' ? 'message-content text-primary dark:text-dark-primary' : ''"
                                x-html="renderMessage(msg.content)"
                            ></div>
                            <p
                                class="text-[10px] mt-1.5 select-none"
                                :class="msg.role === 'user' ? 'text-white/40 text-right' : 'text-tertiary dark:text-dark-tertiary'"
                                x-text="formatTime(msg.created_at)"
                            ></p>
                        </div>
                    </div>
                </template>

                {{-- Typing indicator --}}
                <template x-if="loading">
                    <div class="flex justify-start gap-2 sm:gap-3 animate-fade-in">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-accent to-amber-600 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex items-center gap-1.5 px-4 py-3 rounded-2xl rounded-bl-md bg-surface dark:bg-dark-surface border border-border dark:border-dark-border">
                            <div class="w-1.5 h-1.5 bg-accent rounded-full animate-bounce" style="animation-delay:0ms"></div>
                            <div class="w-1.5 h-1.5 bg-accent rounded-full animate-bounce" style="animation-delay:150ms"></div>
                            <div class="w-1.5 h-1.5 bg-accent rounded-full animate-bounce" style="animation-delay:300ms"></div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ===== INPUT AREA ===== --}}
            <div class="shrink-0 border-t border-border dark:border-dark-border bg-card dark:bg-dark-card px-3 sm:px-4 py-3 sm:py-4">

                {{-- Attached context files chips --}}
                <div class="mb-2.5 flex flex-wrap gap-1.5 sm:gap-2" x-show="contextFiles.length > 0" x-cloak>
                    <template x-for="file in contextFiles" :key="file.id">
                        <div class="flex items-center gap-1.5 sm:gap-2 rounded-lg border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-2 sm:px-2.5 py-1 sm:py-1.5 text-[11px] sm:text-xs group hover:border-accent/30 transition-colors">
                            <svg class="w-3 sm:w-3.5 h-3 sm:h-3.5 text-accent shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span class="max-w-[100px] sm:max-w-[160px] truncate text-primary dark:text-dark-primary" x-text="file.original_name"></span>
                            <span class="text-tertiary dark:text-dark-tertiary hidden sm:inline" x-text="file.formatted_size"></span>
                            <button type="button" @click="removeContextFile(file.id)" class="text-tertiary hover:text-danger dark:hover:text-dark-danger transition-colors ml-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Input row --}}
                <div class="flex items-end gap-2">
                    {{-- File upload trigger --}}
                    <input
                        x-ref="fileInput"
                        type="file"
                        multiple
                        class="hidden"
                        accept=".pdf,.doc,.docx,.txt,.md,.csv,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                        @change="handleFileUpload"
                    >
                    <button
                        type="button"
                        @click="$refs.fileInput.click()"
                        :disabled="uploadingFiles"
                        title="Attach document for analysis"
                        class="p-2 sm:p-2.5 rounded-xl border border-border dark:border-dark-border text-secondary dark:text-dark-secondary hover:text-accent dark:hover:text-dark-accent hover:border-accent/40 transition-colors disabled:opacity-50 shrink-0 mb-px active:scale-95"
                    >
                        <template x-if="!uploadingFiles">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </template>
                        <template x-if="uploadingFiles">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                    </button>

                    {{-- Textarea --}}
                    <div class="flex-1 relative">
                        <textarea
                            x-ref="inputField"
                            x-model="input"
                            rows="1"
                            placeholder="Ask anything..."
                            class="w-full resize-none rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-3 sm:px-4 py-2.5 pr-11 text-[13px] sm:text-sm text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary focus:border-accent dark:focus:border-dark-accent focus:ring-1 focus:ring-accent/30 outline-none transition-all max-h-32 sm:max-h-40 overflow-y-auto scrollbar-thin"
                            :disabled="loading"
                            @keydown.enter="if(!$event.shiftKey){ $event.preventDefault(); send(); }"
                            @input="autoResize($el)"
                        ></textarea>
                        <button
                            type="button"
                            @click="send()"
                            :disabled="loading || !input.trim()"
                            class="absolute right-1.5 sm:right-2 bottom-1.5 sm:bottom-2 w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center rounded-lg bg-accent text-white hover:bg-amber-700 disabled:opacity-30 disabled:cursor-not-allowed transition-all active:scale-90"
                        >
                            <template x-if="!loading">
                                <svg class="w-3.5 sm:w-4 h-3.5 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </template>
                            <template x-if="loading">
                                <svg class="w-3.5 sm:w-4 h-3.5 sm:h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                        </button>
                    </div>
                </div>
                <p class="mt-2 text-[10px] text-tertiary dark:text-dark-tertiary text-center hidden sm:block">Press Enter to send &middot; Shift+Enter for new line &middot; Attach documents for context</p>
                <p class="mt-1.5 text-[10px] text-tertiary dark:text-dark-tertiary text-center sm:hidden">Enter to send &middot; Attach files for context</p>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Message content typography */
        .message-content p { margin-bottom: 0.6em; }
        .message-content p:last-child { margin-bottom: 0; }
        .message-content h1, .message-content h2, .message-content h3 { font-weight: 600; margin-top: 1em; margin-bottom: 0.4em; }
        .message-content h1 { font-size: 1.1rem; }
        .message-content h2 { font-size: 1rem; }
        .message-content h3 { font-size: 0.9rem; }
        .message-content ul, .message-content ol { margin-left: 1.25em; margin-bottom: 0.6em; }
        .message-content li { margin-bottom: 0.15em; }
        .message-content code {
            background: rgba(0,0,0,0.06);
            padding: 0.1em 0.35em;
            border-radius: 0.3rem;
            font-size: 0.82em;
            font-family: 'SF Mono', 'Fira Code', 'Cascadia Code', monospace;
        }
        .dark .message-content code { background: rgba(255,255,255,0.1); }
        .message-content pre {
            background: #1e1e2e;
            color: #cdd6f4;
            padding: 0.85rem 1rem;
            border-radius: 0.6rem;
            overflow-x: auto;
            margin: 0.6em 0;
            font-size: 0.78rem;
            line-height: 1.5;
        }
        .message-content pre code { background: transparent; padding: 0; color: inherit; }
        .message-content blockquote {
            border-left: 3px solid #d97706;
            padding-left: 0.8em;
            margin: 0.6em 0;
            color: #6b7280;
            font-style: italic;
        }
        .message-content a { color: #d97706; text-decoration: underline; }
        .message-content strong { font-weight: 600; }
        .message-content table { width: 100%; border-collapse: collapse; margin: 0.6em 0; font-size: 0.82em; }
        .message-content th, .message-content td { border: 1px solid #e5e7eb; padding: 0.35em 0.6em; text-align: left; }
        .message-content th { background: #f9fafb; font-weight: 600; }
        .dark .message-content th { background: rgba(255,255,255,0.05); }
        .dark .message-content th, .dark .message-content td { border-color: rgba(255,255,255,0.1); }

        /* Fade-in animation */
        .animate-fade-in { animation: fadeIn 0.2s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        /* Line clamp */
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        /* Mobile: full-height chat when bottom nav is present */
        @media (max-width: 1023px) {
            .mobile-bottom-spacing > div:first-child {
                margin-bottom: 0;
            }
        }

        /* Smooth scrollbar for messages */
        .scrollbar-thin { scrollbar-width: thin; scrollbar-color: #E5E5E4 transparent; }
        .dark .scrollbar-thin { scrollbar-color: #3A3A3C transparent; }

        /* Mobile-optimized pre code blocks */
        @media (max-width: 639px) {
            .message-content pre {
                padding: 0.65rem 0.75rem;
                font-size: 0.72rem;
                border-radius: 0.5rem;
            }
            .message-content table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
    function aiChat() {
        return {
            input: '',
            messages: [],
            conversations: [],
            currentConversationId: null,
            currentTitle: '',
            contextFiles: [],
            loading: false,
            uploadingFiles: false,
            sidebarOpen: false,
            searchQuery: '',

            quickActions: [
                { label: 'Summarize a document', hint: 'Attach a file and get a clear summary with key points', prompt: 'Please summarize the attached document and highlight the key points.' },
                { label: 'Grant proposal review', hint: 'Get feedback on clarity, impact and competitiveness', prompt: 'Review this grant proposal and suggest improvements for clarity, impact, and competitiveness.' },
                { label: 'Literature review help', hint: 'Structure a review with key themes and gaps', prompt: 'Help me structure a literature review for my research topic. What are the key themes and gaps I should address?' },
                { label: 'Research methodology', hint: 'Choose and justify the right approach', prompt: 'Help me choose and justify an appropriate research methodology for my study.' },
            ],

            async init() {
                await this.loadConversations();
                // On desktop, show sidebar by default
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = true;
                }
            },

            filteredConversations() {
                if (!this.searchQuery.trim()) return this.conversations;
                const q = this.searchQuery.toLowerCase().trim();
                return this.conversations.filter(c => c.title.toLowerCase().includes(q));
            },

            async loadConversations() {
                try {
                    const res = await axios.get('/api/ai/conversations');
                    this.conversations = res.data;
                } catch(e) { console.error('Failed to load conversations:', e); }
            },

            groupedFilteredConversations() {
                const convs = this.filteredConversations();
                return this._groupConversations(convs);
            },

            _groupConversations(convs) {
                const now = new Date();
                const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
                const weekAgo = new Date(today); weekAgo.setDate(today.getDate() - 7);

                const groups = { 'Today': [], 'Yesterday': [], 'This Week': [], 'Older': [] };
                for (const conv of convs) {
                    const d = new Date(conv.updated_at);
                    const day = new Date(d.getFullYear(), d.getMonth(), d.getDate());
                    if (day >= today) groups['Today'].push(conv);
                    else if (day >= yesterday) groups['Yesterday'].push(conv);
                    else if (day >= weekAgo) groups['This Week'].push(conv);
                    else groups['Older'].push(conv);
                }
                return Object.fromEntries(Object.entries(groups).filter(([, v]) => v.length > 0));
            },

            newChat() {
                this.currentConversationId = null;
                this.currentTitle = '';
                this.messages = [];
                this.contextFiles = [];
                this.input = '';
            },

            async loadConversation(id) {
                try {
                    const res = await axios.get(`/api/ai/conversations/${id}/messages`);
                    this.messages = res.data.messages;
                    this.currentConversationId = res.data.conversation.id;
                    this.currentTitle = res.data.conversation.title;
                    this.contextFiles = [];
                    this.$nextTick(() => this.scrollToBottom());
                } catch(e) { console.error('Failed to load conversation:', e); }
            },

            async deleteConversation(id) {
                if (!confirm('Delete this chat?')) return;
                try {
                    await axios.delete(`/api/ai/conversations/${id}`);
                    if (this.currentConversationId === id) this.newChat();
                    await this.loadConversations();
                } catch(e) { console.error('Failed to delete conversation:', e); }
            },

            async handleFileUpload(event) {
                const files = Array.from(event.target.files || []);
                if (!files.length) return;
                this.uploadingFiles = true;
                try {
                    for (const file of files) {
                        const formData = new FormData();
                        formData.append('file', file);
                        const res = await axios.post('/api/ai/context-files', formData, {
                            headers: { 'Content-Type': 'multipart/form-data' }
                        });
                        if (!this.contextFiles.some(f => f.id === res.data.id)) {
                            this.contextFiles.push(res.data);
                        }
                    }
                } catch(e) {
                    const msg = e.response?.data?.message || e.response?.data?.errors?.file?.[0] || 'Upload failed.';
                    alert(msg);
                } finally {
                    this.uploadingFiles = false;
                    event.target.value = '';
                }
            },

            removeContextFile(id) {
                this.contextFiles = this.contextFiles.filter(f => f.id !== id);
            },

            async send() {
                const content = this.input.trim();
                if (!content || this.loading) return;
                this.input = '';
                this.$nextTick(() => { if (this.$refs.inputField) { this.$refs.inputField.style.height = 'auto'; } });

                // Create conversation if needed
                if (!this.currentConversationId) {
                    try {
                        const res = await axios.post('/api/ai/conversations', {
                            title: content.substring(0, 60) + (content.length > 60 ? '\u2026' : ''),
                            scope: 'general',
                            ai_context_files: this.contextFiles.map(f => f.id),
                        });
                        this.currentConversationId = res.data.id;
                        this.currentTitle = res.data.title;
                    } catch(e) { console.error('Failed to create conversation:', e); return; }
                }

                // Optimistic user message
                this.messages.push({ id: Date.now(), role: 'user', content, created_at: new Date().toISOString() });
                this.loading = true;
                this.scrollToBottom();

                try {
                    const res = await axios.post(`/api/ai/conversations/${this.currentConversationId}/messages`, {
                        message: content,
                        ai_context_files: this.contextFiles.map(f => f.id),
                        use_web_search: false,
                    });
                    this.messages = res.data.conversation.messages;
                    this.currentTitle = res.data.conversation_meta?.title || this.currentTitle;
                    await this.loadConversations();
                } catch(e) {
                    const err = e.response?.data?.error || e.response?.data?.message || 'Something went wrong. Please try again.';
                    this.messages.push({ id: Date.now() + 1, role: 'assistant', content: '\u26a0\ufe0f ' + err, created_at: new Date().toISOString() });
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                }
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const el = this.$refs.messages;
                    if (el) el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
                });
            },

            autoResize(el) {
                el.style.height = 'auto';
                el.style.height = Math.min(el.scrollHeight, window.innerWidth < 640 ? 128 : 160) + 'px';
            },

            renderMessage(text) {
                if (!text) return '';
                let html = text
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(/```(\w+)?\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>')
                    .replace(/`([^`\n]+)`/g, '<code>$1</code>')
                    .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*([^*\n]+)\*/g, '<em>$1</em>')
                    .replace(/^### (.+)$/gm, '<h3>$1</h3>')
                    .replace(/^## (.+)$/gm, '<h2>$1</h2>')
                    .replace(/^# (.+)$/gm, '<h1>$1</h1>')
                    .replace(/^\- (.+)$/gm, '<li>$1</li>')
                    .replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
                    .replace(/(<li>[\s\S]*?<\/li>\n?)+/g, '<ul>$&</ul>')
                    .replace(/\n\n/g, '</p><p>')
                    .replace(/\n/g, '<br>');
                return '<p>' + html + '</p>';
            },

            formatTime(iso) {
                if (!iso) return '';
                return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            },
        };
    }
    </script>
    @endpush
</x-layouts.app>
