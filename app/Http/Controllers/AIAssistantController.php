<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AI\AIHealthService;
use App\Services\AI\Exceptions\AIServiceException;
use App\Services\AI\RAGService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * The Personal Workspace AI Assistant.
 *
 * Handles the chat UI and the JSON endpoints the front-end calls. It NEVER talks
 * to Ollama/Qdrant directly: all AI work flows through the RAGService, and every
 * conversation is scoped to the authenticated user.
 */
class AIAssistantController extends Controller
{
    public function __construct(
        protected RAGService $rag,
        protected AIHealthService $health
    ) {}

    /**
     * The assistant page. Renders gracefully when the local AI is offline.
     */
    public function index(Request $request): View
    {
        $userId = (int) Auth::id();

        $report = $this->health->report($userId);

        $conversations = AiConversation::ownedBy($userId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get();

        $active = null;

        if ($request->filled('conversation')) {
            $active = AiConversation::ownedBy($userId)->find((int) $request->query('conversation'));
        }

        $messages = $active
            ? $active->messages()->get()
            : collect();

        $suggestions = $this->suggestions();

        return view('ai.assistant', compact('report', 'conversations', 'active', 'messages', 'suggestions'));
    }

    /**
     * Answer a question and return the JSON turn for the chat UI.
     */
    public function ask(Request $request): JsonResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'question' => ['required', 'string', 'max:' . (int) config('ai.limits.max_question_chars', 2000)],
            'conversation_id' => ['nullable', 'integer'],
        ]);

        // Local AI must be enabled before doing any work.
        if (! config('ai.enabled')) {
            return response()->json([
                'ok' => false,
                'error' => 'The AI assistant is disabled. Enable it with AI_ENABLED=true.',
            ], 503);
        }

        // Simple per-user throttle to protect the local runtime.
        $key = 'ai-ask:' . $user->id;

        if (RateLimiter::tooManyAttempts($key, (int) config('ai.limits.rate_limit_per_minute', 20))) {
            return response()->json([
                'ok' => false,
                'error' => AIServiceException::defaultMessage(AIServiceException::CODE_RATE_LIMITED),
            ], 429);
        }

        RateLimiter::hit($key, 60);

        // Load recent history (own conversation only).
        $history = [];

        if (! empty($data['conversation_id'])) {
            $conversation = AiConversation::ownedBy($user->id)->find($data['conversation_id']);

            if ($conversation) {
                $history = $conversation->messages()
                    ->orderByDesc('id')
                    ->limit((int) config('ai.limits.history_messages', 6))
                    ->get()
                    ->reverse()
                    ->map(fn(AiMessage $m) => ['role' => $m->role, 'content' => $m->content])
                    ->values()
                    ->all();
            }
        }

        try {
            $result = $this->rag->answer(
                $user,
                $data['question'],
                isset($data['conversation_id']) ? (int) $data['conversation_id'] : null,
                $history,
                'assistant'
            );

            return response()->json([
                'ok' => true,
                'answer' => $result['answer'],
                'conversation_id' => $result['conversation_id'],
                'intent' => $result['intent'],
                'sources' => $result['sources'],
                'degraded' => $result['degraded'],
            ]);
        } catch (AIServiceException $e) {
            // Graceful, safe message; the app keeps working.
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
                'reason' => $e->reason(),
            ], 503);
        }
    }

    /**
     * Delete one of the user's own conversations.
     */
    public function destroy(AiConversation $conversation): JsonResponse
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * A small set of example prompts shown in the UI.
     *
     * @return array<int, string>
     */
    protected function suggestions(): array
    {
        return [
            'What did I do today?',
            'What did I do during the last 7 days?',
            'What goals am I currently working on?',
            'How is my goal progress?',
            'Which habits have I been consistent with?',
            'What events do I have this week?',
            'How much did I spend this month?',
            'What were my biggest expense categories?',
            'What income did I receive this month?',
            'Summarize my week.',
            'Find activities related to learning programming.',
        ];
    }
}
