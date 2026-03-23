<x-layouts.app title="My Reports">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span>My Reports</span>
        </div>
    </x-slot:header>

    {{-- Report Tabs --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-1 bg-surface rounded-xl p-1 border border-border">
            <button onclick="showTab('reports')" id="tab-reports" class="tab-btn px-4 py-2 text-xs font-medium rounded-lg bg-accent text-white shadow-sm transition-all">Progress Reports</button>
            <button onclick="showTab('documents')" id="tab-documents" class="tab-btn px-4 py-2 text-xs font-medium rounded-lg text-secondary hover:text-primary transition-all">My Documents</button>
        </div>
        <a href="{{ route('reports.create', $student) }}" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-accent text-white hover:bg-amber-700 transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Report
        </a>
    </div>

    {{-- Reports Tab Content --}}
    <div id="content-reports" class="tab-content">
        <div class="bg-card rounded-2xl border border-border overflow-hidden">
            <div class="p-6 border-b border-border">
                <h3 class="text-base font-semibold text-primary">Progress Reports</h3>
                <p class="text-xs text-secondary mt-1">Track your research progress with regular reports</p>
            </div>
            <div class="divide-y divide-border">
                @if($reports->count() > 0)
                    @foreach($reports as $report)
                    <a href="{{ route('reports.show', [$student, $report]) }}" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
                        <div class="w-10 h-10 rounded-xl @if($report->status === 'submitted') bg-info/10 text-info @elseif($report->status === 'reviewed') bg-success/10 text-success @elseif($report->status === 'revision_needed') bg-warning/10 text-warning @else bg-tertiary/10 text-tertiary @endif flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors">{{ $report->title }}</p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <p class="text-xs text-secondary">{{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}</p>
                                <span class="text-xs text-tertiary">•</span>
                                <p class="text-xs text-secondary">{{ $report->type_label }}</p>
                            </div>
                        </div>
                        <x-status-badge :status="$report->status" size="sm" />
                        <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @endforeach
                @else
                    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-primary mb-2">No reports yet</h3>
                        <p class="text-sm text-secondary max-w-sm mb-6">Start tracking your progress by submitting your first research report.</p>
                        <a href="{{ route('reports.create', $student) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-accent text-white hover:bg-amber-700 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create First Report
                        </a>
                    </div>
                @endif
            </div>
            @if($reports->hasPages())
            <div class="p-4 border-t border-border flex items-center justify-between">
                <p class="text-xs text-secondary">Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} reports</p>
                {{ $reports->appends(['search' => request()->search])->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Documents Tab Content --}}
    <div id="content-documents" class="tab-content hidden">
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Upload Section -->
            <div class="lg:col-span-1">
                <x-card title="Upload Document" :padding="'loose'">
                    <form action="{{ route('files.upload', $student) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Document Category -->
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Document Category</label>
                            <select name="category" required class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                <option value="">Select category...</option>
                                <option value="thesis">📚 Thesis</option>
                                <option value="manuscript">📄 Manuscript</option>
                                <option value="proposal">📋 Proposal</option>
                                <option value="report">📊 Report</option>
                                <option value="presentation">🎤 Presentation</option>
                                <option value="simulation">💻 Simulation</option>
                                <option value="data">📈 Data</option>
                                <option value="images">🖼️ Images</option>
                                <option value="references">📚 References</option>
                                <option value="other">📁 Other</option>
                            </select>
                        </div>

                        <!-- File Upload -->
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Document File</label>
                            <div class="relative">
                                <input type="file" name="file" required
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.jpg,.jpeg,.png,.gif"
                                    class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-accent/10 file:text-accent hover:file:bg-accent/20"
                                    onchange="updateFileName(this)">
                                <p class="text-[10px] text-tertiary mt-1">PDF, Word, Excel, PowerPoint, ZIP (Max 50MB)</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Description (Optional)</label>
                            <textarea name="description" rows="2"
                                placeholder="Brief description of this document..."
                                class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all resize-none"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-lg bg-accent text-white hover:bg-amber-700 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Document
                        </button>
                    </form>
                </x-card>

                <!-- Category Legend -->
                <x-card title="Category Guide" :padding="'tight'">
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-surface">
                            <span class="text-lg">📚</span>
                            <div>
                                <p class="font-medium text-primary">Thesis</p>
                                <p class="text-secondary">Main thesis document</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-surface">
                            <span class="text-lg">📄</span>
                            <div>
                                <p class="font-medium text-primary">Manuscript</p>
                                <p class="text-secondary">Research manuscripts</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-surface">
                            <span class="text-lg">📋</span>
                            <div>
                                <p class="font-medium text-primary">Proposal</p>
                                <p class="text-secondary">Research proposals</p>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Documents List -->
            <div class="lg:col-span-2">
                <x-card :padding="'loose'">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-primary">My Documents</h3>
                        <div class="flex items-center gap-2">
                            <select id="categoryFilter" onchange="filterDocuments()" class="px-3 py-1.5 text-xs border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                <option value="">All Categories</option>
                                <option value="thesis">📚 Thesis</option>
                                <option value="manuscript">📄 Manuscript</option>
                                <option value="proposal">📋 Proposal</option>
                                <option value="report">📊 Report</option>
                                <option value="presentation">🎤 Presentation</option>
                                <option value="simulation">💻 Simulation</option>
                                <option value="data">📈 Data</option>
                                <option value="images">🖼️ Images</option>
                                <option value="references">📚 References</option>
                                <option value="other">📁 Other</option>
                            </select>
                        </div>
                    </div>

                    <div id="documentsList" class="space-y-3">
                        @foreach($student->files()->where('is_latest', true)->latest()->take(20)->get() as $file)
                        <div class="document-item flex items-center gap-3 p-3 rounded-xl border border-border hover:border-accent/30 hover:shadow-soft transition-all" data-category="{{ $file->category ?? 'other' }}">
                            <div class="w-10 h-10 rounded-xl @if($file->category === 'thesis') bg-purple-100 text-purple-600 @elseif($file->category === 'manuscript') bg-blue-100 text-blue-600 @elseif($file->category === 'proposal') bg-green-100 text-green-600 @elseif($file->category === 'report') bg-amber-100 text-amber-600 @elseif($file->category === 'presentation') bg-pink-100 text-pink-600 @elseif($file->category === 'simulation') bg-cyan-100 text-cyan-600 @elseif($file->category === 'data') bg-indigo-100 text-indigo-600 @elseif($file->category === 'images') bg-rose-100 text-rose-600 @elseif($file->category === 'references') bg-teal-100 text-teal-600 @else bg-gray-100 text-gray-600 @endif flex items-center justify-center shrink-0 text-lg">
                                {{ $file->category ? $file->category == 'thesis' ? '📚' : $file->category == 'manuscript' ? '📄' : $file->category == 'proposal' ? '📋' : $file->category == 'report' ? '📊' : $file->category == 'presentation' ? '🎤' : $file->category == 'simulation' ? '💻' : $file->category == 'data' ? '📈' : $file->category == 'images' ? '🖼️' : $file->category == 'references' ? '📚' : '📁' : '📁' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-primary truncate">{{ $file->original_name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <p class="text-xs text-tertiary">{{ $file->sizeForHumans() }}</p>
                                    <span class="text-xs text-tertiary">•</span>
                                    <p class="text-xs text-tertiary">{{ \Carbon\Carbon::parse($file->created_at)->format('M d, Y') }}</p>
                                    @if($file->description)
                                    <span class="text-xs text-tertiary">•</span>
                                    <p class="text-xs text-secondary truncate max-w-[150px]">{{ $file->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <a href="{{ route('files.download', [$student, $file]) }}" class="p-2 text-secondary hover:text-primary hover:bg-surface rounded-lg transition-all" title="Download">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                </a>
                                <button onclick="deleteFile({{ $file->id }})" class="p-2 text-secondary hover:text-danger hover:bg-danger/10 rounded-lg transition-all" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if($student->files()->where('is_latest', true)->count() === 0)
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-primary mb-1">No documents yet</h3>
                        <p class="text-xs text-secondary">Upload your research documents to get started</p>
                    </div>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
<script>
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('bg-accent', 'text-white', 'shadow-sm');
        el.classList.add('text-secondary');
    });

    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    const activeBtn = document.getElementById('tab-' + tabName);
    activeBtn.classList.add('bg-accent', 'text-white', 'shadow-sm');
    activeBtn.classList.remove('text-secondary');
}

function updateFileName(input) {
    if (input.files && input.files[0]) {
        const fileName = input.files[0].name;
        // Update display if needed
    }
}

function filterDocuments() {
    const category = document.getElementById('categoryFilter').value;
    document.querySelectorAll('.document-item').forEach(item => {
        if (category === '' || item.dataset.category === category) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

async function deleteFile(fileId) {
    if (!confirm('Are you sure you want to delete this document?')) return;

    try {
        const response = await axios.delete(`/students/{{ $student->id }}/files/${fileId}`);
        location.reload();
    } catch (error) {
        alert('Failed to delete document');
    }
}
</script>
@endpush
