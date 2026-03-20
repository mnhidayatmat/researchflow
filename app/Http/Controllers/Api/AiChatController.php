<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\Ai\AiChatService;
use App\Services\Ai\AiServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function conversations(Request $request)
    {
        $conversations = AiConversation::where('user_id', Auth::id())
            ->withCount('messages')
            ->latest()
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title ?? 'New Chat',
                    'scope' => $conversation->scope,
                    'student_id' => $conversation->student_id,
                    'created_at' => $conversation->created_at->toISOString(),
                    'updated_at' => $conversation->updated_at->toISOString(),
                    'messages_count' => $conversation->messages_count ?? 0,
                ];
            });

        return response()->json($conversations);
    }

    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'student_id' => 'nullable|exists:students,id',
            'scope' => 'nullable|in:general,student,planning,proposal,analysis,writing',
            'context_files' => 'nullable|array',
        ]);

        $conversation = AiConversation::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'] ?? 'New Chat',
            'student_id' => $validated['student_id'] ?? null,
            'scope' => $validated['scope'] ?? 'general',
            'context_files' => $validated['context_files'] ?? [],
        ]);

        return response()->json($conversation, 201);
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
                ];
            });

        return response()->json($messages);
    }

    public function sendMessage(Request $request, AiConversation $conversation)
    {
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'use_rag' => 'nullable|boolean',
        ]);

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

        $chatService = new AiChatService($provider);
        $response = $chatService->chat($messages, $systemPrompt, $validated['use_rag'] ?? false, $conversation);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $response,
            'metadata' => ['provider' => class_basename(get_class($provider))],
        ]);

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
        ]);
    }

    protected function getSystemPrompt(?string $scope, ?int $studentId): string
    {
        $basePrompt = "You are a helpful AI assistant for a research supervision management system. You assist students, supervisors, and administrators with research-related tasks.";

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
}
