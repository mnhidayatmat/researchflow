<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiContextFile;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiProject;
use App\Services\Ai\AiChatService;
use App\Services\Ai\Cowork\AiCoworkService;
use App\Services\Ai\AiServiceFactory;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AiChatController extends Controller
{
    public function projects(Request $request)
    {
        $projects = AiProject::where('user_id', Auth::id())
            ->with(['conversations' => function ($query) {
                $query->has('messages')
                    ->withCount('messages')
                    ->withMax('messages', 'created_at')
                    ->orderByDesc('messages_max_created_at');
            }])
            ->withCount('conversations')
            ->latest('updated_at')
            ->get()
            ->map(fn (AiProject $project) => $this->serializeProject($project));

        return response()->json($projects);
    }

    public function createProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'student_id' => 'nullable|exists:students,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = AiProject::create([
            'user_id' => Auth::id(),
            'student_id' => $validated['student_id'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($this->serializeProject($project->load('conversations')), 201);
    }

    public function deleteProject(Request $request, AiProject $project)
    {
        if ($project->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function conversations(Request $request)
    {
        $conversations = AiConversation::where('user_id', Auth::id())
            ->withCount('messages')
            ->latest('updated_at')
            ->limit(100)
            ->get()
            ->map(fn (AiConversation $c) => $this->serializeConversation($c));

        return response()->json($conversations);
    }

    public function uploadContextFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,txt,md,csv,xls,xlsx,ppt,pptx,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store('ai-context/' . Auth::id(), 'local');

        $record = AiContextFile::create([
            'user_id'       => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
        ]);

        return response()->json([
            'id'            => $record->id,
            'original_name' => $record->original_name,
            'size'          => $record->size,
            'formatted_size' => $record->formatted_size,
        ], 201);
    }

    public function deleteContextFile(Request $request, AiContextFile $contextFile)
    {
        abort_unless($contextFile->user_id === Auth::id(), 403);
        Storage::disk('local')->delete($contextFile->path);
        $contextFile->delete();
        return response()->json(['success' => true]);
    }

    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:ai_projects,id',
            'title' => 'nullable|string|max:255',
            'student_id' => 'nullable|exists:students,id',
            'scope' => 'nullable|in:general,student,planning,proposal,analysis,writing,cowork',
            'context_files' => 'nullable|array',
            'context_files.*' => 'integer|exists:files,id',
            'ai_context_files' => 'nullable|array',
            'ai_context_files.*' => 'integer|exists:ai_context_files,id',
            'metadata' => 'nullable|array',
        ]);

        if (!empty($validated['project_id'])) {
            $project = AiProject::whereKey($validated['project_id'])
                ->where('user_id', Auth::id())
                ->firstOrFail();
        } else {
            $project = AiProject::firstOrCreate(
                ['user_id' => Auth::id(), 'name' => 'General'],
                ['user_id' => Auth::id(), 'name' => 'General']
            );
        }

        $conversation = AiConversation::create([
            'user_id'          => Auth::id(),
            'project_id'       => $project->id,
            'title'            => $validated['title'] ?? 'New Chat',
            'student_id'       => $validated['student_id'] ?? null,
            'scope'            => $validated['scope'] ?? 'general',
            'context_files'    => $validated['context_files'] ?? [],
            'metadata'         => array_merge($validated['metadata'] ?? [], [
                'ai_context_files' => $validated['ai_context_files'] ?? [],
            ]),
        ]);

        $project->touch();

        return response()->json($this->serializeConversation($conversation), 201);
    }

    public function messages(Request $request, AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toISOString(),
                    'metadata' => $message->metadata,
                ];
            });

        return response()->json([
            'conversation' => $this->serializeConversation($conversation->loadCount('messages')),
            'messages' => $messages,
        ]);
    }

    public function deleteConversation(Request $request, AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $projectId = $conversation->project_id;

        $conversation->delete();

        if ($projectId) {
            AiProject::whereKey($projectId)->update(['updated_at' => now()]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function sendMessage(Request $request, AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message'           => 'required|string',
            'use_rag'           => 'nullable|boolean',
            'use_web_search'    => 'nullable|boolean',
            'context_files'     => 'nullable|array',
            'context_files.*'   => 'integer|exists:files,id',
            'ai_context_files'  => 'nullable|array',
            'ai_context_files.*'=> 'integer|exists:ai_context_files,id',
        ]);

        if (array_key_exists('context_files', $validated)) {
            $conversation->update([
                'context_files' => $validated['context_files'] ?? [],
            ]);
        }

        // Persist AI context file IDs in conversation metadata
        if (!empty($validated['ai_context_files'])) {
            $conversation->update([
                'metadata' => array_merge($conversation->metadata ?? [], [
                    'ai_context_files' => $validated['ai_context_files'],
                ]),
            ]);
        }

        $provider = AiServiceFactory::getProvider();

        if (!$provider) {
            return response()->json([
                'error' => 'No AI provider configured. Please configure an AI provider in settings.',
            ], 400);
        }

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'    => 'user',
            'content' => $validated['message'],
        ]);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn ($msg) => ['role' => $msg->role, 'content' => $msg->content])
            ->toArray();

        $systemPrompt = $this->getSystemPrompt($conversation->scope, $conversation->student_id);

        // Inject uploaded context file content into system prompt
        $aiContextFileIds = $validated['ai_context_files']
            ?? $conversation->metadata['ai_context_files']
            ?? [];

        if (!empty($aiContextFileIds)) {
            $contextFiles = AiContextFile::whereIn('id', $aiContextFileIds)
                ->where('user_id', Auth::id())
                ->get();

            $fileContext = '';
            foreach ($contextFiles as $ctxFile) {
                if (!Storage::disk('local')->exists($ctxFile->path)) {
                    continue;
                }

                $content = $this->extractFileContent($ctxFile);
                if ($content) {
                    $content = mb_substr($content, 0, 12000); // cap per file
                    $fileContext .= "\n\n--- Document: {$ctxFile->original_name} ---\n{$content}";
                }
            }

            if ($fileContext) {
                $systemPrompt .= "\n\nThe user has provided the following documents as context:{$fileContext}";
            }
        }

        try {
            set_time_limit(120);

            $chatService = new AiChatService($provider);
            $response = $chatService->chatWithMessages(
                $messages,
                $systemPrompt,
                $validated['use_rag'] ?? false,
                $conversation,
                [
                    'use_web_search' => $validated['use_web_search'] ?? false,
                ]
            );
        } catch (Throwable $e) {
            $status = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600
                ? $e->getCode()
                : 500;

            return response()->json([
                'error' => $e->getMessage(),
            ], $status);
        }

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $response,
            'metadata' => ['provider' => class_basename(get_class($provider))],
        ]);

        $conversation->touch();
        $conversation->project?->touch();

        if (!$conversation->title || $conversation->title === 'New Chat') {
            $firstUserMessage = $conversation->messages()->where('role', 'user')->first();
            if ($firstUserMessage) {
                $title = substr($firstUserMessage->content, 0, 50);
                if (strlen($firstUserMessage->content) > 50) {
                    $title .= '...';
                }
                $conversation->update(['title' => $title]);
            }
        }

        // Fetch all messages for the conversation
        $allMessages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toISOString(),
                    'metadata' => $message->metadata,
                ];
            });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'messages' => $allMessages,
            ],
            'conversation_meta' => $this->serializeConversation($conversation->loadCount('messages')),
        ]);
    }

    public function coworkMessage(Request $request, AiConversation $conversation, AiCoworkService $coworkService)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'workspace_path' => 'required|string|max:1000',
            'context_files' => 'nullable|array',
            'context_files.*' => 'integer|exists:files,id',
        ]);

        $conversation->update([
            'scope' => 'cowork',
            'context_files' => $validated['context_files'] ?? $conversation->context_files ?? [],
            'metadata' => [
                ...($conversation->metadata ?? []),
                'mode' => 'cowork',
                'workspace_path' => $validated['workspace_path'],
            ],
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
            'metadata' => [
                'mode' => 'cowork',
                'workspace_path' => $validated['workspace_path'],
            ],
        ]);

        try {
            $result = $coworkService->execute(Auth::user(), $validated['message'], $validated['workspace_path']);
        } catch (Throwable $e) {
            $status = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600
                ? $e->getCode()
                : 422;

            return response()->json([
                'error' => $e->getMessage(),
            ], $status);
        }

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $result['message'],
            'metadata' => [
                'provider' => 'ZAiProvider',
                ...($result['metadata'] ?? []),
            ],
        ]);

        $conversation->update([
            'metadata' => [
                ...($conversation->metadata ?? []),
                'mode' => 'cowork',
                'workspace_path' => $result['metadata']['workspace_path'] ?? $validated['workspace_path'],
            ],
        ]);
        $conversation->touch();
        $conversation->project?->touch();

        $allMessages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toISOString(),
                    'metadata' => $message->metadata,
                ];
            });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'messages' => $allMessages,
            ],
            'conversation_meta' => $this->serializeConversation($conversation->loadCount('messages')),
        ]);
    }

    public function coworkPlan(Request $request, AiConversation $conversation, AiCoworkService $coworkService)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'workspace_label' => 'required|string|max:255',
            'workspace_context' => 'required|array',
        ]);

        try {
            $plan = $coworkService->plan(Auth::user(), $validated['message'], $validated['workspace_context']);
        } catch (Throwable $e) {
            $status = is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600
                ? $e->getCode()
                : 422;

            return response()->json([
                'error' => $e->getMessage(),
            ], $status);
        }

        $conversation->update([
            'scope' => 'cowork',
            'metadata' => [
                ...($conversation->metadata ?? []),
                'mode' => 'cowork',
                'workspace_label' => $validated['workspace_label'],
                'workspace_source' => 'browser',
            ],
        ]);

        return response()->json([
            'plan' => $plan,
            'conversation_meta' => $this->serializeConversation($conversation->fresh()->loadCount('messages')),
        ]);
    }

    public function coworkComplete(Request $request, AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'workspace_label' => 'required|string|max:255',
            'plan' => 'required|array',
            'execution_result' => 'required|array',
        ]);

        $conversation->update([
            'scope' => 'cowork',
            'metadata' => [
                ...($conversation->metadata ?? []),
                'mode' => 'cowork',
                'workspace_label' => $validated['workspace_label'],
                'workspace_source' => 'browser',
            ],
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
            'metadata' => [
                'mode' => 'cowork',
                'workspace_label' => $validated['workspace_label'],
            ],
        ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $this->formatCoworkCompletionMessage($validated['plan'], $validated['execution_result'], $validated['workspace_label']),
            'metadata' => [
                'provider' => 'ZAiProvider',
                'mode' => 'cowork',
                'workspace_label' => $validated['workspace_label'],
                'operation' => $validated['execution_result']['operation'] ?? ($validated['plan']['operation'] ?? null),
                'target' => $validated['execution_result']['relative_path'] ?? ($validated['plan']['relative_path'] ?? null),
                'summary' => $validated['execution_result']['summary'] ?? null,
            ],
        ]);

        $conversation->touch();
        $conversation->project?->touch();

        if (!$conversation->title || $conversation->title === 'New Chat') {
            $conversation->update([
                'title' => substr($validated['message'], 0, 50) . (strlen($validated['message']) > 50 ? '...' : ''),
            ]);
        }

        $allMessages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at->toISOString(),
                    'metadata' => $message->metadata,
                ];
            });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'messages' => $allMessages,
            ],
            'conversation_meta' => $this->serializeConversation($conversation->fresh()->loadCount('messages')),
        ]);
    }

    public function browseCoworkDirectories(Request $request, \App\Services\Ai\Cowork\LocalWorkspaceService $workspaceService)
    {
        $validated = $request->validate([
            'path' => 'nullable|string|max:1000',
        ]);

        try {
            $browser = $workspaceService->browse($validated['path'] ?? null);
        } catch (Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return response()->json($browser);
    }

    protected function serializeConversation(AiConversation $conversation): array
    {
        $metadata = $conversation->metadata ?? [];
        return [
            'id'               => $conversation->id,
            'project_id'       => $conversation->project_id,
            'title'            => $conversation->title ?? 'New Chat',
            'scope'            => $conversation->scope,
            'student_id'       => $conversation->student_id,
            'context_files'    => $conversation->context_files ?? [],
            'ai_context_files' => $metadata['ai_context_files'] ?? [],
            'metadata'         => $metadata,
            'created_at'       => $conversation->created_at->toISOString(),
            'updated_at'       => $conversation->updated_at->toISOString(),
            'messages_count'   => $conversation->messages_count ?? $conversation->messages()->count(),
        ];
    }

    protected function serializeProject(AiProject $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'student_id' => $project->student_id,
            'created_at' => $project->created_at->toISOString(),
            'updated_at' => $project->updated_at->toISOString(),
            'conversations_count' => $project->conversations_count ?? $project->conversations()->count(),
            'conversations' => $project->conversations
                ->map(fn (AiConversation $conversation) => $this->serializeConversation($conversation))
                ->values(),
        ];
    }

    protected function getSystemPrompt(?string $scope, ?int $studentId): string
    {
        $effectiveRole = session()->get('admin_role_switch', Auth::user()->role);
        $basePrompt = "You are a helpful AI assistant for a research supervision management system. You assist students, supervisors, and administrators with research-related tasks.";
        $basePrompt .= "\nCurrent user role context: {$effectiveRole}.";
        $basePrompt .= "\nIf web search is enabled, use it for current literature, recent developments, or time-sensitive claims.";

        if (!$scope || $scope === 'general') {
            return $basePrompt;
        }

        $scopePrompts = [
            'student' => "You are assisting a research student with their progress. Be encouraging and provide practical advice on research methodology, time management, and academic writing.",
            'planning' => "You are helping plan a research project. Provide guidance on research design, methodology selection, and creating realistic timelines.",
            'proposal' => "You are helping with a thesis proposal. Advise on structure, literature review, problem formulation, and research questions.",
            'analysis' => "You are assisting with data analysis. Provide guidance on statistical methods, data interpretation, and presenting findings.",
            'writing' => "You are helping with academic writing. Provide advice on structure, clarity, citations, and maintaining scholarly tone.",
        ];

        $prompt = $scopePrompts[$scope] ?? $basePrompt;

        if ($studentId) {
            $student = \App\Models\Student::with(['user', 'programme'])->find($studentId);
            if ($student) {
                $prompt .= "\n\nStudent Context:\n";
                $prompt .= "- Name: {$student->user->name}\n";
                $prompt .= "- Programme: {$student->programme->name}\n";
                $prompt .= "- Status: {$student->status}\n";
                if ($student->research_title) {
                    $prompt .= "- Research Title: {$student->research_title}\n";
                }
            }
        }

        return $prompt;
    }

    protected function formatCoworkCompletionMessage(array $plan, array $result, string $workspaceLabel): string
    {
        $lines = [
            '**Cowork completed the request on your local workspace.**',
            '',
            '- Workspace: `' . $workspaceLabel . '`',
            '- Operation: ' . ucfirst($result['operation'] ?? ($plan['operation'] ?? 'unknown')),
            '- Target: `' . ($result['relative_path'] ?? ($plan['relative_path'] ?? '')) . '`',
            '- Summary: ' . ($result['summary'] ?? ($plan['summary'] ?? 'Completed.')),
        ];

        if (!empty($result['preview'])) {
            $lines[] = '';
            $lines[] = 'Preview:';
            $lines[] = '```';
            $lines[] = $result['preview'];
            $lines[] = '```';
        }

        return implode("\n", $lines);
    }

    protected function extractFileContent(AiContextFile $ctxFile): ?string
    {
        $fullPath = Storage::disk('local')->path($ctxFile->path);
        $ext = strtolower(pathinfo($ctxFile->original_name, PATHINFO_EXTENSION));
        $maxChars = 12000;

        try {
            // Plain text files
            if (in_array($ext, ['txt', 'md', 'csv', 'html'])) {
                return mb_substr(Storage::disk('local')->get($ctxFile->path), 0, $maxChars);
            }

            // PDF — use pdftotext CLI (memory-safe), fallback to PHP parser for small files
            if ($ext === 'pdf') {
                // Try pdftotext first (poppler-utils) — processes externally, no PHP memory issue
                $pdftotext = collect(['/usr/bin/pdftotext', '/usr/local/bin/pdftotext'])
                    ->first(fn ($p) => is_file($p));

                if ($pdftotext) {
                    $tmpOut = tempnam(sys_get_temp_dir(), 'pdf_');
                    $escaped = escapeshellarg($fullPath);
                    $escapedOut = escapeshellarg($tmpOut);
                    exec("{$pdftotext} -layout -l 30 {$escaped} {$escapedOut} 2>/dev/null", $out, $code);

                    if ($code === 0 && file_exists($tmpOut)) {
                        $text = file_get_contents($tmpOut);
                        @unlink($tmpOut);
                        return mb_substr(trim($text), 0, $maxChars) ?: null;
                    }
                    @unlink($tmpOut);
                }

                // Fallback: PHP parser only for files under 5MB
                $fileSize = filesize($fullPath);
                if ($fileSize > 5 * 1024 * 1024) {
                    return "[PDF too large to parse in-memory (" . round($fileSize / 1024 / 1024, 1) . "MB). Install poppler-utils on the server for large PDF support: sudo apt install poppler-utils]";
                }

                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($fullPath);
                $text = mb_substr($pdf->getText(), 0, $maxChars);
                unset($pdf, $parser);

                return $text ?: null;
            }

            // DOCX — extract text from XML inside the ZIP
            if ($ext === 'docx') {
                $zip = new \ZipArchive();
                if ($zip->open($fullPath) === true) {
                    $xml = $zip->getFromName('word/document.xml');
                    $zip->close();
                    if ($xml) {
                        $text = strip_tags(str_replace('<', ' <', $xml));
                        return mb_substr(preg_replace('/\s+/', ' ', trim($text)), 0, $maxChars);
                    }
                }
                return null;
            }

            // DOC (older format) — basic text extraction
            if ($ext === 'doc') {
                // Skip large .doc files
                if (filesize($fullPath) > 10 * 1024 * 1024) {
                    return "[Document too large for inline analysis: {$ctxFile->original_name}]";
                }
                $content = file_get_contents($fullPath);
                $text = '';
                $len = strlen($content);
                for ($i = 0; $i < $len && strlen($text) < $maxChars; $i++) {
                    $ord = ord($content[$i]);
                    if ($ord >= 32 && $ord <= 126 || $ord === 10 || $ord === 13 || $ord === 9) {
                        $text .= $content[$i];
                    }
                }
                unset($content);
                $text = preg_replace('/\s+/', ' ', trim($text));
                return strlen($text) > 100 ? $text : null;
            }

            // Excel (xlsx, xls)
            if (in_array($ext, ['xlsx', 'xls'])) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
                $text = '';
                foreach ($spreadsheet->getAllSheets() as $sheet) {
                    foreach ($sheet->toArray(null, true, true, true) as $row) {
                        $rowText = implode(' | ', array_filter(array_map('trim', array_map('strval', $row))));
                        if ($rowText) {
                            $text .= $rowText . "\n";
                        }
                        if (mb_strlen($text) >= $maxChars) break 2;
                    }
                }
                unset($spreadsheet);
                return $text ?: null;
            }

            // PowerPoint (pptx)
            if ($ext === 'pptx') {
                $zip = new \ZipArchive();
                if ($zip->open($fullPath) === true) {
                    $text = '';
                    for ($i = 1; $i <= 200; $i++) {
                        $slideXml = $zip->getFromName("ppt/slides/slide{$i}.xml");
                        if (!$slideXml) break;
                        $slideText = strip_tags(str_replace('<', ' <', $slideXml));
                        $slideText = preg_replace('/\s+/', ' ', trim($slideText));
                        if ($slideText) {
                            $text .= "--- Slide {$i} ---\n{$slideText}\n\n";
                        }
                        if (mb_strlen($text) >= $maxChars) break;
                    }
                    $zip->close();
                    return $text ?: null;
                }
                return null;
            }
        } catch (\Throwable $e) {
            \Log::warning("AI context file extraction failed for {$ctxFile->original_name}: {$e->getMessage()}");
            return "[Could not extract text from {$ctxFile->original_name}: {$e->getMessage()}]";
        }

        return null;
    }
}
