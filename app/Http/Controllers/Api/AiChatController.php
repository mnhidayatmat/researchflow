<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiConversation;
use App\Services\Ai\AiChatService;
use App\Services\Ai\AiRagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiChatController extends Controller
{
    public function __construct(
        private AiChatService $chat,
        private AiRagService $rag
    ) {}

    public function conversations()
    {
        $conversations = $this->chat->getUserConversations(Auth::id())
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'scope' => $conversation->scope,
                    'student_id' => $conversation->student_id,
                    'created_at' => $conversation->created_at,
                    'updated_at' => $conversation->updated_at,
                    'last_message' => $conversation->messages()
                        ->latest()
                        ->first(['content', 'created_at']),
                ];
            });

        return response()->json($conversations);
    }

    public function show(AiConversation $conversation)
    {
        $this->authorize('view', $conversation);

        return response()->json($this->chat->getConversation($conversation));
    }

    public function messages(AiConversation $conversation)
    {
        $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get(['id', 'role', 'content', 'metadata', 'created_at']);

        return response()->json($messages);
    }

    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'student_id' => 'nullable|exists:students,id',
            'context_files' => 'nullable|array',
            'context_files.*' => 'exists:files,id',
            'scope' => 'nullable|in:general,student,planning,proposal,analysis,writing',
        ]);

        $conversation = $this->chat->createConversation([
            'title' => $validated['title'] ?? 'New Conversation',
            'student_id' => $validated['student_id'] ?? null,
            'context_files' => $validated['context_files'] ?? null,
            'scope' => $validated['scope'] ?? 'general',
        ]);

        return response()->json($conversation->load('student'), 201);
    }

    public function sendMessage(Request $request, AiConversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'use_rag' => 'nullable|boolean',
        ]);

        // Check if RAG is enabled and context files exist
        $useRag = $validated['use_rag'] ?? false;
        $contextFiles = $useRag ? ($conversation->context_files ?? []) : null;

        try {
            if ($useRag && !empty($contextFiles)) {
                // Use RAG service
                $history = $conversation->messages()
                    ->orderBy('created_at')
                    ->take(20)
                    ->get()
                    ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
                    ->toArray();

                $history[] = ['role' => 'user', 'content' => $validated['content']];

                $response = $this->rag->chatWithRag($history, $contextFiles);

                // Save messages
                $conversation->messages()->create([
                    'role' => 'user',
                    'content' => $validated['content'],
                ]);

                $assistantMsg = $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $response,
                    'metadata' => ['rag_enabled' => true],
                ]);
            } else {
                // Use standard chat
                $result = $this->chat->chat($conversation, $validated['content']);
                $assistantMsg = $result['assistant_message'];
            }

            return response()->json([
                'conversation' => $conversation->load('messages'),
                'response' => $assistantMsg,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function regenerateMessage(Request $request, AiConversation $conversation)
    {
        $this->authorize('update', $conversation);

        // Remove the last assistant message if exists
        $lastMessage = $conversation->messages()
            ->where('role', 'assistant')
            ->latest()
            ->first();

        if ($lastMessage) {
            $lastMessage->delete();
        }

        // Get the last user message
        $lastUserMessage = $conversation->messages()
            ->where('role', 'user')
            ->latest()
            ->first();

        if (!$lastUserMessage) {
            return response()->json(['error' => 'No user message to respond to'], 400);
        }

        try {
            $result = $this->chat->chat($conversation, $lastUserMessage->content);

            return response()->json([
                'response' => $result['assistant_message'],
                'conversation' => $conversation->load('messages'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to regenerate: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateConversation(Request $request, AiConversation $conversation)
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'scope' => 'sometimes|required|in:general,student,planning,proposal,analysis,writing',
            'context_files' => 'sometimes|nullable|array',
            'context_files.*' => 'exists:files,id',
        ]);

        if (isset($validated['context_files'])) {
            $this->chat->updateContextFiles($conversation, $validated['context_files']);
            unset($validated['context_files']);
        }

        $conversation->update($validated);

        return response()->json($conversation);
    }

    public function deleteConversation(AiConversation $conversation)
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return response()->json(['message' => 'Conversation deleted.']);
    }
}
