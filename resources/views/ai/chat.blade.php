<x-layouts.app title="AI Assistant">
    <x-slot:header>AI Assistant</x-slot:header>

    @php
        $roleLabel = ucfirst($effectiveRole ?? auth()->user()->role);
        $studentOptions = $availableStudents->map(fn($contextStudent) => [
            'id' => $contextStudent->id,
            'name' => $contextStudent->user->name,
            'programme' => $contextStudent->programme->code ?? ($contextStudent->programme->name ?? 'No programme'),
        ])->values();
        $initialFiles = $files->map(fn($file) => [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'size_human' => $file->sizeForHumans(),
            'disk' => $file->disk,
        ])->values();
    @endphp

    <div x-data="aiChat()" x-init="init()" class="flex gap-4 h-[calc(100vh-8rem)]">
        {{-- Left: Projects --}}
        <div class="w-72 shrink-0 hidden lg:flex lg:flex-col gap-4">
            <x-card :padding="'none'" class="overflow-hidden">
                <div class="p-3 border-b border-border bg-surface/50 flex items-center justify-between">
                    <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Projects</p>
                    <button @click="creatingProject = !creatingProject; if (!creatingProject) newProjectName = ''" class="text-[10px] text-accent hover:underline">+ New</button>
                </div>
                <div class="p-3 border-b border-border/60" x-show="creatingProject">
                    <div class="space-y-2">
                        <input x-model="newProjectName" type="text" placeholder="Project name" class="w-full rounded-lg border border-border px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                        <div class="flex items-center gap-2">
                            <button @click="createProject()" class="rounded-lg bg-accent px-3 py-1.5 text-[10px] font-medium text-white">Create</button>
                            <button @click="creatingProject = false; newProjectName = ''" class="text-[10px] text-secondary hover:text-primary">Cancel</button>
                        </div>
                    </div>
                </div>
                <div class="max-h-[28rem] overflow-y-auto p-2 space-y-2">
                    <template x-for="project in projects" :key="project.id">
                        <div class="rounded-xl border border-border/80 bg-white">
                            <div class="flex items-center justify-between px-3 py-2" :class="currentProjectId === project.id ? 'bg-amber-50' : ''">
                                <button @click="openProject(project.id)" class="min-w-0 flex-1 text-left">
                                    <p class="truncate text-xs font-semibold" :class="currentProjectId === project.id ? 'text-accent' : 'text-primary'" x-text="project.name"></p>
                                    <p class="text-[10px] text-tertiary" x-text="`${project.conversations.length} chats`"></p>
                                </button>
                                <div class="ml-2 flex items-center gap-2">
                                    <button @click="currentProjectId = project.id; clearChat()" class="text-[10px] text-accent hover:underline">+ Chat</button>
                                    <button @click="deleteProject(project.id)" class="text-[10px] text-secondary hover:text-red-600">Delete</button>
                                </div>
                            </div>
                            <div x-show="currentProjectId === project.id" class="border-t border-border/60 p-2 space-y-1">
                                <template x-for="conv in project.conversations" :key="conv.id">
                                    <div class="flex items-center gap-1">
                                        <button @click="loadConversation(conv.id)" class="min-w-0 flex-1 truncate rounded-lg px-2.5 py-2 text-left text-xs transition-colors" :class="currentConversation === conv.id ? 'bg-amber-50 text-accent' : 'text-secondary hover:bg-surface hover:text-primary'" x-text="conv.title"></button>
                                        <button @click="deleteConversation(conv.id)" class="rounded-lg px-2 py-2 text-[10px] text-secondary hover:bg-red-50 hover:text-red-600" title="Delete chat">Delete</button>
                                    </div>
                                </template>
                                <template x-if="project.conversations.length === 0">
                                    <p class="px-2.5 py-2 text-[10px] text-secondary">No chats yet in this project.</p>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template x-if="projects.length === 0">
                        <p class="text-xs text-secondary text-center py-4">No projects yet</p>
                    </template>
                </div>
            </x-card>

            <x-card :padding="'none'" class="overflow-hidden">
                <div class="p-3 border-b border-border bg-surface/50">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Mode</p>
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">Premium</span>
                    </div>
                </div>
                <div class="space-y-3 p-3">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="mode = 'chat'" :class="mode === 'chat' ? 'border-accent bg-amber-50 text-accent' : 'border-border text-secondary'" class="rounded-lg border px-3 py-2 text-xs font-medium transition-colors">Chat</button>
                        <button type="button" @click="mode = 'cowork'" :class="mode === 'cowork' ? 'border-accent bg-amber-50 text-accent' : 'border-border text-secondary'" class="rounded-lg border px-3 py-2 text-xs font-medium transition-colors">Cowork</button>
                    </div>
                    <div x-show="mode === 'cowork'" x-cloak class="space-y-2">
                        <label class="block text-[10px] font-semibold uppercase tracking-wider text-secondary">Local workspace</label>
                        <div class="flex gap-2">
                            <input x-model="workspacePath" type="text" readonly placeholder="Select a local folder from your device" class="flex-1 rounded-lg border border-border bg-white px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                            <button type="button" @click="pickLocalWorkspace()" class="rounded-lg border border-border px-3 py-2 text-xs font-medium text-primary hover:bg-surface">Select Folder</button>
                        </div>
                        <p class="text-[10px] text-secondary">Your browser will ask you to choose a real local folder. Cowork edits files there directly when your browser supports the File System Access API.</p>
                        <div x-show="localWorkspaceNodes.length > 0" x-cloak class="rounded-xl border border-border bg-white">
                            <div class="flex items-center justify-between border-b border-border px-3 py-2">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-secondary">Workspace Tree</p>
                                <span class="text-[10px] text-secondary" x-text="selectedLocalFiles.length ? `${selectedLocalFiles.length} selected` : `${localWorkspaceEntries.length} entries`"></span>
                            </div>
                            <div class="max-h-64 overflow-y-auto p-2 space-y-1">
                                <template x-for="node in localWorkspaceNodes" :key="node.path">
                                    <div>
                                        <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs hover:bg-surface">
                                            <button type="button" class="w-5 shrink-0 text-tertiary" @click="node.kind === 'directory' ? toggleTreeNode(node.path) : null">
                                                <span x-text="node.kind === 'directory' ? (isTreeNodeExpanded(node.path) ? '-' : '+') : ''"></span>
                                            </button>
                                            <template x-if="node.kind === 'file'">
                                                <input type="checkbox" class="rounded border-gray-300 text-accent focus:ring-accent" :checked="selectedLocalFiles.includes(node.path)" @change="toggleLocalFileSelection(node.path)">
                                            </template>
                                            <template x-if="node.kind === 'directory'">
                                                <span class="w-4 shrink-0 text-[10px] font-semibold text-amber-700">DIR</span>
                                            </template>
                                            <button type="button" class="min-w-0 flex-1 text-left" @click="node.kind === 'directory' ? toggleTreeNode(node.path) : previewLocalFile(node.path)">
                                                <div class="flex items-center gap-2" :style="`padding-left:${node.depth * 14}px`">
                                                    <span class="truncate text-primary" x-text="node.name"></span>
                                                    <span class="shrink-0 text-[10px] text-tertiary" x-show="node.kind === 'file' && node.size_human" x-text="node.size_human"></span>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <div class="border-t border-border px-3 py-2 text-[10px] text-secondary">
                                Select one or more files to prioritize them in Cowork context. Click a file name to preview text content.
                            </div>
                        </div>
                        <div x-show="localFilePreview.path" x-cloak class="rounded-xl border border-border bg-white">
                            <div class="flex items-center justify-between border-b border-border px-3 py-2">
                                <p class="truncate text-[10px] font-semibold uppercase tracking-wider text-secondary" x-text="localFilePreview.path"></p>
                                <button type="button" class="text-[10px] text-secondary hover:text-primary" @click="clearLocalFilePreview()">Close</button>
                            </div>
                            <div class="max-h-48 overflow-auto p-3">
                                <pre class="whitespace-pre-wrap break-words text-[11px] leading-relaxed text-primary" x-text="localFilePreview.content"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card :padding="'none'" class="overflow-hidden">
                <div class="p-3 border-b border-border bg-surface/50">
                    <p class="text-xs font-semibold text-secondary uppercase tracking-wider">Student</p>
                </div>
                <div class="p-3">
                    <template x-if="availableStudents.length > 0">
                        <select x-model.number="studentId" @change="handleStudentChange()" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                            <option value="">Select student context</option>
                            <template x-for="contextStudent in availableStudents" :key="contextStudent.id">
                                <option :value="contextStudent.id" x-text="`${contextStudent.name} | ${contextStudent.programme}`"></option>
                            </template>
                        </select>
                    </template>
                    <template x-if="availableStudents.length === 0">
                        <p class="text-xs text-secondary">No student context available.</p>
                    </template>
                </div>
            </x-card>

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

                    <label class="mt-3 flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="useWebSearch" class="rounded border-gray-300 text-accent focus:ring-accent">
                        <span class="text-xs font-medium text-primary">Use Web Search</span>
                    </label>
                    <p class="text-[10px] text-secondary mt-1">Use for latest literature, recent papers, and current information when supported by the active provider</p>
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
                        <p class="text-[10px] text-secondary">
                            <span x-text="mode === 'cowork' ? '{{ $roleLabel }} cowork workspace' : '{{ $roleLabel }} workspace'"></span>
                            @if($student)
                                • {{ $student->user->name }}
                            @endif
                        </p>
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
                        <p class="text-xs text-secondary max-w-sm">
                            <span x-show="mode === 'chat'">Ask about research planning, supervisor feedback, document analysis, literature review, or operational admin work.</span>
                            <span x-show="mode === 'cowork'">Select a real local folder from your device, then ask Cowork to read, create, update, list, or delete files inside it.</span>
                            @if($student)
                                Current context: {{ $student->user->name }}.
                            @endif
                        </p>
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
                                <template x-if="msg.metadata?.operation">
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700" x-text="msg.metadata.operation"></span>
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
                <div class="mb-3 flex flex-wrap items-center gap-2" x-show="mode === 'chat'">
                    <input x-ref="fileUpload" type="file" multiple class="hidden" accept=".png,.jpg,.jpeg,.gif,.webp,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.ppt,.pptx,.zip,.rar,.7z" @change="handleLocalUpload">
                    <button type="button" @click="triggerUpload()" class="rounded-lg border border-border px-3 py-2 text-xs font-medium text-primary hover:bg-surface disabled:opacity-50" :disabled="uploadingFiles">
                        <span x-text="uploadingFiles ? 'Uploading...' : 'Upload photo & files'"></span>
                    </button>
                    <button type="button" @click="openFilePicker()" class="rounded-lg border border-border px-3 py-2 text-xs font-medium text-primary hover:bg-surface">
                        <span x-text="storageDisk === 'google_drive' ? 'Add from Google Drive' : 'Add existing files'"></span>
                    </button>
                    <span class="text-[10px] text-secondary" x-show="!studentId">Select a student to enable attachments.</span>
                </div>

                <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50/60 px-3 py-2 text-xs text-secondary" x-show="mode === 'cowork'">
                    Cowork requires premium access and a Chromium-based browser with local folder permissions.
                </div>

                <div class="mb-3 flex flex-wrap gap-2" x-show="mode === 'chat' && selectedAttachments().length > 0">
                    <template x-for="file in selectedAttachments()" :key="file.id">
                        <div class="flex items-center gap-2 rounded-xl border border-border bg-surface px-3 py-2 text-xs">
                            <span class="max-w-44 truncate" x-text="file.original_name"></span>
                            <span class="text-[10px] text-tertiary" x-text="file.size_human || 'File'"></span>
                            <button type="button" @click="removeAttachment(file.id)" class="text-secondary hover:text-red-600">x</button>
                        </div>
                    </template>
                </div>

                <form @submit.prevent="send()" class="flex gap-3">
                    <div class="flex-1 relative">
                        <input x-model="input" type="text" :placeholder="mode === 'cowork' ? 'Example: update this Blade file and add a cowork mode badge' : 'Ask about your research...'" class="w-full rounded-xl border border-border bg-surface px-4 py-2.5 pr-24 text-sm focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-all" :disabled="loading" @keydown.ctrl.enter="send()" @keydown.meta.enter="send()">
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

        <div x-show="showFilePicker" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-primary/20 backdrop-blur-sm px-4">
            <div class="w-full max-w-2xl rounded-2xl border border-border bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-primary" x-text="storageDisk === 'google_drive' ? 'Add from Google Drive' : 'Add Existing Files'"></p>
                        <p class="text-[10px] text-secondary">Select files to attach as context to this chat.</p>
                    </div>
                    <button type="button" @click="showFilePicker = false" class="text-secondary hover:text-primary">Close</button>
                </div>
                <div class="border-b border-border px-4 py-3">
                    <input x-model="fileSearch" type="text" placeholder="Search files..." class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="max-h-[28rem] overflow-y-auto p-3 space-y-2">
                    <template x-for="file in filteredAvailableFiles()" :key="file.id">
                        <label class="flex cursor-pointer items-center justify-between rounded-xl border border-border px-3 py-2 hover:bg-surface">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-primary" x-text="file.original_name"></p>
                                <p class="text-[10px] text-secondary">
                                    <span x-text="file.size_human || 'File'"></span>
                                    <span x-show="file.disk"> | <span x-text="file.disk"></span></span>
                                </p>
                            </div>
                            <input type="checkbox" class="rounded border-gray-300 text-accent focus:ring-accent" :checked="contextFiles.includes(file.id)" @change="toggleAttachment(file.id)">
                        </label>
                    </template>
                    <template x-if="filteredAvailableFiles().length === 0">
                        <p class="py-6 text-center text-sm text-secondary">No matching files found.</p>
                    </template>
                </div>
            </div>
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
                projects: [],
                currentConversation: null,
                currentProjectId: null,
                mode: 'chat',
                workspacePath: '',
                studentId: {{ $student?->id ?? 'null' }},
                availableStudents: @js($studentOptions),
                storageDisk: @js($currentStorageDisk),
                availableFiles: @js($initialFiles),
                contextFiles: [],
                useRag: false,
                loading: false,
                creatingProject: false,
                newProjectName: '',
                showFilePicker: false,
                fileSearch: '',
                uploadingFiles: false,
                localWorkspaceHandle: null,
                localWorkspaceSupported: typeof window.showDirectoryPicker === 'function',
                localWorkspaceEntries: [],
                localWorkspaceNodes: [],
                expandedTreeNodes: [''],
                selectedLocalFiles: [],
                localFilePreview: {
                    path: '',
                    content: '',
                },

                quickActions: [
                    { label: 'Summarize latest report', prompt: 'Summarize my latest progress report and highlight key achievements and challenges.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>' },
                    { label: 'Check deadline risks', prompt: 'What tasks are at risk of missing their deadline? Please analyze and prioritize.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                    { label: 'Suggest next tasks', prompt: 'Based on my current progress, what should be my next 3-5 tasks?', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>' },
                    { label: 'Methodology review', prompt: 'Review my research methodology and suggest improvements.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>' },
                    { label: 'Writing assistance', prompt: 'Help me improve my academic writing style and clarity.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' },
                    { label: 'Latest literature review', prompt: 'Find the latest literature review and recent papers for my research topic, then summarize the main themes and gaps.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/></svg>' },
                    { label: 'Build literature matrix', prompt: 'Create a literature matrix with columns for author, year, objective, method, dataset, findings, limitations, and relevance to my study.', icon: '<svg class="w-3 h-3 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 6h18M3 14h18M3 18h18"/></svg>' },
                ],
                useWebSearch: false,

                async init() {
                    if (!this.studentId && this.availableStudents.length === 1) {
                        this.studentId = this.availableStudents[0].id;
                    }
                    await this.refreshProjects();
                    await this.refreshAvailableFiles();
                },

                async handleStudentChange() {
                    this.contextFiles = [];
                    this.currentConversation = null;
                    this.messages = [];
                    await this.refreshAvailableFiles();
                },

                async refreshProjects() {
                    try {
                        const res = await axios.get('/api/ai/projects');
                        this.projects = res.data;

                        if (!this.currentProjectId && this.projects.length > 0) {
                            this.currentProjectId = this.projects[0].id;
                        }
                    } catch(e) {
                        console.error('Failed to load projects:', e);
                    }
                },

                async refreshAvailableFiles() {
                    if (!this.studentId) {
                        this.availableFiles = [];
                        return;
                    }

                    try {
                        const res = await axios.get(`/api/students/${this.studentId}/files`, {
                            params: {
                                per_page: 100,
                                sort_by: 'created_at',
                                sort_order: 'desc',
                            }
                        });
                        this.availableFiles = res.data.data ?? [];
                    } catch (e) {
                        console.error('Failed to load files:', e);
                    }
                },

                triggerUpload() {
                    if (!this.studentId) {
                        alert('Select a student context first.');
                        return;
                    }

                    this.$refs.fileUpload.click();
                },

                openFilePicker() {
                    if (!this.studentId) {
                        alert('Select a student context first.');
                        return;
                    }

                    this.showFilePicker = true;
                },

                async pickLocalWorkspace() {
                    if (!this.localWorkspaceSupported) {
                        alert('This browser does not support local folder access. Use a recent Chromium-based browser.');
                        return;
                    }

                    try {
                        const handle = await window.showDirectoryPicker({ mode: 'readwrite' });
                        this.localWorkspaceHandle = handle;
                        this.workspacePath = handle.name;
                        this.selectedLocalFiles = [];
                        this.localFilePreview = { path: '', content: '' };
                        await this.loadLocalWorkspaceEntries();
                    } catch (e) {
                        if (e?.name !== 'AbortError') {
                            console.error('Failed to select local workspace:', e);
                            alert('Failed to select local folder.');
                        }
                    }
                },

                async verifyWorkspacePermission() {
                    if (!this.localWorkspaceHandle) return false;

                    const current = await this.localWorkspaceHandle.queryPermission({ mode: 'readwrite' });
                    if (current === 'granted') return true;

                    const requested = await this.localWorkspaceHandle.requestPermission({ mode: 'readwrite' });
                    return requested === 'granted';
                },

                async loadLocalWorkspaceEntries() {
                    if (!this.localWorkspaceHandle) {
                        this.localWorkspaceEntries = [];
                        this.localWorkspaceNodes = [];
                        return;
                    }

                    const entries = [];
                    const nodes = [];
                    const maxEntries = 160;
                    const maxDepth = 4;

                    const walk = async (directoryHandle, prefix = '', depth = 0) => {
                        if (depth > maxDepth || entries.length >= maxEntries) return;

                        const currentEntries = [];
                        for await (const [name, handle] of directoryHandle.entries()) {
                            currentEntries.push([name, handle]);
                        }

                        currentEntries.sort((a, b) => {
                            if (a[1].kind !== b[1].kind) {
                                return a[1].kind === 'directory' ? -1 : 1;
                            }

                            return a[0].localeCompare(b[0]);
                        });

                        for (const [name, handle] of currentEntries) {
                            if (entries.length >= maxEntries) break;

                            const entry = {
                                name,
                                path: prefix ? `${prefix}/${name}` : name,
                                kind: handle.kind,
                                depth,
                            };

                            if (handle.kind === 'file') {
                                try {
                                    const file = await handle.getFile();
                                    entry.size = file.size;
                                    entry.size_human = this.formatBytes(file.size);
                                } catch (e) {
                                    entry.size_human = '';
                                }
                            }

                            entries.push(entry);
                            nodes.push(entry);

                            if (handle.kind === 'directory') {
                                await walk(handle, entry.path, depth + 1);
                            }
                        }
                    };

                    await walk(this.localWorkspaceHandle);

                    this.localWorkspaceEntries = entries;
                    this.localWorkspaceNodes = nodes.filter((node) => this.isNodeVisible(node.path));
                    this.expandedTreeNodes = [''];
                },

                isTreeNodeExpanded(path) {
                    return this.expandedTreeNodes.includes(path);
                },

                isNodeVisible(path) {
                    const parent = path.includes('/') ? path.substring(0, path.lastIndexOf('/')) : '';
                    return parent === '' || this.expandedTreeNodes.includes(parent);
                },

                refreshVisibleTreeNodes() {
                    this.localWorkspaceNodes = this.localWorkspaceEntries.filter((node) => this.isNodeVisible(node.path));
                },

                toggleTreeNode(path) {
                    if (this.expandedTreeNodes.includes(path)) {
                        this.expandedTreeNodes = this.expandedTreeNodes.filter((item) => item !== path);
                    } else {
                        this.expandedTreeNodes.push(path);
                    }

                    this.refreshVisibleTreeNodes();
                },

                toggleLocalFileSelection(path) {
                    if (this.selectedLocalFiles.includes(path)) {
                        this.selectedLocalFiles = this.selectedLocalFiles.filter((item) => item !== path);
                        return;
                    }

                    this.selectedLocalFiles.push(path);
                },

                async previewLocalFile(path) {
                    if (!this.localWorkspaceHandle || !this.isTextLikeFile(path)) {
                        return;
                    }

                    try {
                        const { directory, fileName } = await this.resolveLocalTarget(path);
                        const handle = await directory.getFileHandle(fileName);
                        const file = await handle.getFile();
                        const content = await file.text();
                        this.localFilePreview = {
                            path,
                            content: content.length > 6000 ? `${content.slice(0, 6000)}\n\n[preview truncated]` : content,
                        };
                    } catch (e) {
                        console.error('Failed to preview local file:', e);
                    }
                },

                clearLocalFilePreview() {
                    this.localFilePreview = { path: '', content: '' };
                },

                async buildWorkspaceContext() {
                    if (!this.localWorkspaceHandle) {
                        throw new Error('Select a local folder first.');
                    }

                    const entries = [];
                    const files = [];
                    let totalFiles = 0;
                    const maxEntries = 120;
                    const maxTextFiles = 8;
                    const maxDepth = 3;
                    const prioritized = new Set(this.selectedLocalFiles);

                    const walk = async (directoryHandle, prefix = '', depth = 0) => {
                        if (depth > maxDepth || entries.length >= maxEntries) return;

                        for await (const [name, handle] of directoryHandle.entries()) {
                            if (entries.length >= maxEntries) break;

                            const relativePath = prefix ? `${prefix}/${name}` : name;
                            entries.push({
                                path: relativePath,
                                type: handle.kind,
                            });

                            if (handle.kind === 'file') {
                                totalFiles++;
                                const shouldInclude = prioritized.size === 0 || prioritized.has(relativePath);
                                if (files.length < maxTextFiles && shouldInclude && this.isTextLikeFile(name)) {
                                    const file = await handle.getFile();
                                    if (file.size <= 25000) {
                                        files.push({
                                            path: relativePath,
                                            size: file.size,
                                            content: await file.text(),
                                        });
                                    }
                                }
                            } else if (handle.kind === 'directory') {
                                await walk(handle, relativePath, depth + 1);
                            }
                        }
                    };

                    await walk(this.localWorkspaceHandle);

                    return {
                        workspace_label: this.workspacePath || this.localWorkspaceHandle.name,
                        root_name: this.localWorkspaceHandle.name,
                        scanned_entry_count: entries.length,
                        total_files_seen: totalFiles,
                        selected_files: Array.from(prioritized),
                        entries,
                        text_files: files,
                    };
                },

                isTextLikeFile(name) {
                    const lower = (name || '').toLowerCase();
                    return ['.php', '.blade.php', '.js', '.ts', '.tsx', '.jsx', '.json', '.md', '.txt', '.css', '.scss', '.html', '.xml', '.yml', '.yaml', '.sql', '.csv']
                        .some((ext) => lower.endsWith(ext));
                },

                async applyCoworkPlan(plan) {
                    const relativePath = (plan.relative_path || '').replace(/^\/+/, '');
                    if (!relativePath && plan.operation !== 'list') {
                        throw new Error('Cowork did not return a target path.');
                    }

                    switch (plan.operation) {
                        case 'list':
                            return await this.listLocalDirectory(relativePath);
                        case 'read':
                            return await this.readLocalFile(relativePath);
                        case 'create':
                            return plan.target_type === 'directory'
                                ? await this.createLocalDirectory(relativePath)
                                : await this.writeLocalFile(relativePath, plan.content || '', true);
                        case 'update':
                            return await this.writeLocalFile(relativePath, plan.content || '', false);
                        case 'delete':
                            return plan.target_type === 'directory'
                                ? await this.deleteLocalDirectory(relativePath)
                                : await this.deleteLocalFile(relativePath);
                        default:
                            throw new Error(plan.clarification || 'Unsupported Cowork operation.');
                    }
                },

                async resolveLocalTarget(relativePath, createDirectories = false) {
                    const segments = (relativePath || '').split('/').filter(Boolean);
                    const fileName = segments.pop();
                    let current = this.localWorkspaceHandle;

                    for (const segment of segments) {
                        current = await current.getDirectoryHandle(segment, { create: createDirectories });
                    }

                    return { directory: current, fileName };
                },

                async listLocalDirectory(relativePath = '') {
                    let directoryHandle = this.localWorkspaceHandle;
                    if (relativePath) {
                        for (const segment of relativePath.split('/').filter(Boolean)) {
                            directoryHandle = await directoryHandle.getDirectoryHandle(segment);
                        }
                    }

                    const entries = [];
                    for await (const [name, handle] of directoryHandle.entries()) {
                        entries.push(`${name} (${handle.kind})`);
                    }

                    return {
                        operation: 'list',
                        relative_path: relativePath || '.',
                        summary: 'Listed local directory contents.',
                        preview: entries.join('\n'),
                    };
                },

                async readLocalFile(relativePath) {
                    const { directory, fileName } = await this.resolveLocalTarget(relativePath);
                    const handle = await directory.getFileHandle(fileName);
                    const file = await handle.getFile();
                    const content = await file.text();

                    return {
                        operation: 'read',
                        relative_path: relativePath,
                        summary: 'Read local file contents.',
                        preview: content.length > 4000 ? `${content.slice(0, 4000)}\n\n[preview truncated]` : content,
                    };
                },

                async writeLocalFile(relativePath, content, createNew) {
                    const { directory, fileName } = await this.resolveLocalTarget(relativePath, true);
                    const handle = await directory.getFileHandle(fileName, { create: true });
                    const writable = await handle.createWritable();
                    await writable.write(content);
                    await writable.close();

                    return {
                        operation: createNew ? 'create' : 'update',
                        relative_path: relativePath,
                        summary: createNew ? 'Created local file.' : 'Updated local file.',
                        preview: content.length > 4000 ? `${content.slice(0, 4000)}\n\n[preview truncated]` : content,
                    };
                },

                async createLocalDirectory(relativePath) {
                    let current = this.localWorkspaceHandle;
                    for (const segment of relativePath.split('/').filter(Boolean)) {
                        current = await current.getDirectoryHandle(segment, { create: true });
                    }

                    return {
                        operation: 'create',
                        relative_path: relativePath,
                        summary: 'Created local directory.',
                        preview: null,
                    };
                },

                async deleteLocalFile(relativePath) {
                    const { directory, fileName } = await this.resolveLocalTarget(relativePath);
                    await directory.removeEntry(fileName);

                    return {
                        operation: 'delete',
                        relative_path: relativePath,
                        summary: 'Deleted local file.',
                        preview: null,
                    };
                },

                async deleteLocalDirectory(relativePath) {
                    const segments = relativePath.split('/').filter(Boolean);
                    const name = segments.pop();
                    let current = this.localWorkspaceHandle;
                    for (const segment of segments) {
                        current = await current.getDirectoryHandle(segment);
                    }
                    await current.removeEntry(name, { recursive: true });

                    return {
                        operation: 'delete',
                        relative_path: relativePath,
                        summary: 'Deleted local directory.',
                        preview: null,
                    };
                },

                async createProject() {
                    const name = this.newProjectName.trim();
                    if (!name) return;

                    try {
                        const res = await axios.post('/api/ai/projects', {
                            name,
                            student_id: this.studentId,
                        });

                        this.projects.unshift(res.data);
                        this.currentProjectId = res.data.id;
                        this.currentConversation = null;
                        this.messages = [];
                        this.newProjectName = '';
                        this.creatingProject = false;
                    } catch(e) {
                        console.error('Failed to create project:', e);
                    }
                },

                filteredAvailableFiles() {
                    const term = this.fileSearch.trim().toLowerCase();
                    if (!term) return this.availableFiles;

                    return this.availableFiles.filter((file) => {
                        return (file.original_name || '').toLowerCase().includes(term);
                    });
                },

                selectedAttachments() {
                    return this.availableFiles.filter((file) => this.contextFiles.includes(file.id));
                },

                toggleAttachment(id) {
                    const numericId = Number(id);
                    if (this.contextFiles.includes(numericId)) {
                        this.contextFiles = this.contextFiles.filter((fileId) => fileId !== numericId);
                        return;
                    }

                    this.contextFiles.push(numericId);
                },

                removeAttachment(id) {
                    const numericId = Number(id);
                    this.contextFiles = this.contextFiles.filter((fileId) => fileId !== numericId);
                },

                async handleLocalUpload(event) {
                    const selectedFiles = Array.from(event.target.files || []);
                    if (!this.studentId || selectedFiles.length === 0) {
                        event.target.value = '';
                        return;
                    }

                    const formData = new FormData();
                    selectedFiles.forEach((file) => formData.append('files[]', file));
                    formData.append('category', 'references');

                    this.uploadingFiles = true;

                    try {
                        const res = await axios.post(`/api/students/${this.studentId}/files/upload-multiple`, formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data',
                            }
                        });

                        const uploaded = res.data.files || [];
                        uploaded.forEach((file) => {
                            file.size_human = file.size_human || this.formatBytes(file.size || 0);
                            if (!this.availableFiles.some((existing) => existing.id === file.id)) {
                                this.availableFiles.unshift(file);
                            }
                            if (!this.contextFiles.includes(file.id)) {
                                this.contextFiles.push(file.id);
                            }
                        });
                    } catch (e) {
                        console.error('Failed to upload files:', e);
                    } finally {
                        this.uploadingFiles = false;
                        event.target.value = '';
                    }
                },

                async deleteProject(id) {
                    if (!confirm('Delete this project and all chats inside it?')) return;

                    try {
                        await axios.delete(`/api/ai/projects/${id}`);

                        if (this.currentProjectId === id) {
                            this.currentProjectId = null;
                            this.currentConversation = null;
                            this.messages = [];
                        }

                        await this.refreshProjects();
                    } catch (e) {
                        console.error('Failed to delete project:', e);
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
                    this.contextFiles = [];
                    this.input = '';
                },

                openProject(projectId) {
                    this.currentProjectId = projectId;
                    this.currentConversation = null;
                    this.contextFiles = [];
                    this.messages = [];
                },

                async loadConversation(id) {
                    try {
                        const res = await axios.get(`/api/ai/conversations/${id}/messages`);
                        this.messages = res.data.messages;
                        this.currentConversation = res.data.conversation.id;
                        this.currentProjectId = res.data.conversation.project_id;
                        this.studentId = res.data.conversation.student_id || this.studentId;
                        this.contextFiles = res.data.conversation.context_files || [];
                        this.mode = res.data.conversation.metadata?.mode === 'cowork' ? 'cowork' : 'chat';
                        this.workspacePath = res.data.conversation.metadata?.workspace_label || res.data.conversation.metadata?.workspace_path || '';
                        this.$nextTick(() => this.scrollToBottom());
                    } catch(e) {
                        console.error('Failed to load conversation:', e);
                    }
                },

                async deleteConversation(id) {
                    if (!confirm('Delete this chat?')) return;

                    try {
                        await axios.delete(`/api/ai/conversations/${id}`);

                        if (this.currentConversation === id) {
                            this.currentConversation = null;
                            this.messages = [];
                        }

                        await this.refreshProjects();
                    } catch (e) {
                        console.error('Failed to delete conversation:', e);
                    }
                },

                async send() {
                    if (!this.input.trim() || this.loading) return;
                    if (this.mode === 'cowork' && !this.localWorkspaceHandle) {
                        alert('Select a local folder first.');
                        return;
                    }

                    const content = this.input;
                    this.input = '';

                    if (!this.currentProjectId) {
                        this.creatingProject = true;
                        this.newProjectName = this.newProjectName || 'New Project';
                        await this.createProject();
                    }

                    // Create conversation if needed
                    if (!this.currentConversation) {
                        try {
                            const res = await axios.post('/api/ai/conversations', {
                                project_id: this.currentProjectId,
                                title: content.substring(0, 50) + (content.length > 50 ? '...' : ''),
                                student_id: this.studentId,
                                context_files: this.contextFiles,
                                scope: this.mode === 'cowork' ? 'cowork' : (this.studentId ? 'student' : 'general'),
                                metadata: {
                                    mode: this.mode,
                                    workspace_label: this.workspacePath || null,
                                    workspace_source: this.mode === 'cowork' ? 'browser' : null,
                                }
                            });
                            this.currentConversation = res.data.id;
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
                        let res;
                        if (this.mode === 'cowork') {
                            const granted = await this.verifyWorkspacePermission();
                            if (!granted) {
                                throw new Error('Local folder permission was not granted.');
                            }

                            const workspaceContext = await this.buildWorkspaceContext();
                            const planRes = await axios.post(`/api/ai/conversations/${this.currentConversation}/cowork-plan`, {
                                message: content,
                                workspace_label: this.workspacePath,
                                workspace_context: workspaceContext,
                            });

                            const executionResult = await this.applyCoworkPlan(planRes.data.plan);

                            res = await axios.post(`/api/ai/conversations/${this.currentConversation}/cowork-complete`, {
                                message: content,
                                workspace_label: this.workspacePath,
                                plan: planRes.data.plan,
                                execution_result: executionResult,
                            });
                        } else {
                            res = await axios.post(`/api/ai/conversations/${this.currentConversation}/messages`, {
                                message: content,
                                use_rag: this.useRag,
                                use_web_search: this.useWebSearch,
                                context_files: this.contextFiles,
                            });
                        }

                        this.messages = res.data.conversation.messages;
                        this.currentConversation = res.data.conversation_meta.id;
                        this.currentProjectId = res.data.conversation_meta.project_id;
                        this.contextFiles = res.data.conversation_meta.context_files || this.contextFiles;
                        this.mode = res.data.conversation_meta.metadata?.mode === 'cowork' ? 'cowork' : this.mode;
                        this.workspacePath = res.data.conversation_meta.metadata?.workspace_label || res.data.conversation_meta.metadata?.workspace_path || this.workspacePath;
                        await this.refreshProjects();
                    } catch(e) {
                        console.error('Chat error:', e);
                        let errorMsg = 'Sorry, something went wrong. Please try again.';
                        if (e.response && e.response.data) {
                            if (e.response.data.error) {
                                errorMsg = 'Error: ' + e.response.data.error;
                            }
                            if (e.response.data.message) {
                                errorMsg = 'Error: ' + e.response.data.message;
                            }
                        }
                        this.messages.push({
                            id: Date.now() + 1,
                            role: 'assistant',
                            content: errorMsg,
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

                formatBytes(bytes) {
                    const units = ['B', 'KB', 'MB', 'GB'];
                    let value = Number(bytes || 0);
                    let unitIndex = 0;

                    while (value >= 1024 && unitIndex < units.length - 1) {
                        value /= 1024;
                        unitIndex++;
                    }

                    return `${value.toFixed(value >= 10 || unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`;
                },

                truncateText(text, maxLength) {
                    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
