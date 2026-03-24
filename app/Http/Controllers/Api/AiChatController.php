<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\AiProject;
use App\Services\Ai\AiChatService;
use App\Services\Ai\Cowork\AiCoworkService;
use App\Services\Ai\AiServiceFactory;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:ai_projects,id',
            'title' => 'nullable|string|max:255',
            'student_id' => 'nullable|exists:students,id',
            'scope' => 'nullable|in:general,student,planning,proposal,analysis,writing,cowork',
            'context_files' => 'nullable|array',
            'context_files.*' => 'integer|exists:files,id',
            'metadata' => 'nullable|array',
        ]);

        $project = AiProject::whereKey($validated['project_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation = AiConversation::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'title' => $validated['title'] ?? 'New Chat',
            'student_id' => $validated['student_id'] ?? $project->student_id,
            'scope' => $validated['scope'] ?? 'general',
            'context_files' => $validated['context_files'] ?? [],
            'metadata' => $validated['metadata'] ?? [],
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
            'message' => 'required|string',
            'use_rag' => 'nullable|boolean',
            'use_web_search' => 'nullable|boolean',
            'context_files' => 'nullable|array',
            'context_files.*' => 'integer|exists:files,id',
        ]);

        if (array_key_exists('context_files', $validated)) {
            $conversation->update([
                'context_files' => $validated['context_files'] ?? [],
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
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->content,
                ];
            })
            ->toArray();

        $systemPrompt = $this->getSystemPrompt($conversation->scope, $conversation->student_id);

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
        return [
            'id' => $conversation->id,
            'project_id' => $conversation->project_id,
            'title' => $conversation->title ?? 'New Chat',
            'scope' => $conversation->scope,
            'student_id' => $conversation->student_id,
            'context_files' => $conversation->context_files ?? [],
            'metadata' => $conversation->metadata ?? [],
            'created_at' => $conversation->created_at->toISOString(),
            'updated_at' => $conversation->updated_at->toISOString(),
            'messages_count' => $conversation->messages_count ?? $conversation->messages()->count(),
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
}
