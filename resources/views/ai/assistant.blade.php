@php
/** Small helper to render a nice status pill for the health report. */
$badgeClass = fn (string $status): string => match ($status) {
'Healthy' => 'badge-income',
'Warning' => 'badge-active',
default => 'badge-expense',
};
@endphp
<x-app-layout>
    <x-slot name="title">AI Assistant - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">AI Assistant</x-slot>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">AI Assistant <span class="badge-active" style="margin-left:0.5rem;">100% Local</span></h2>
                <p class="card-subtitle">
                    Ask about your activities, goals, habits, events and finances. Answers are generated on your own
                    machine through a local model. No paid AI API is used and your data never leaves this computer.
                </p>
            </div>
            <div>
                <span class="badge {{ $badgeClass($report['status']) }}">{{ $report['status'] }}</span>
            </div>
        </div>
    </div>

    @if(! $report['enabled'])
    <div class="alert-danger">
        <strong>The AI assistant is currently disabled.</strong>
        Set <code>AI_ENABLED=true</code> in your <code>.env</code>, make sure Ollama is running, then reload this page.
        Every other feature in your workspace keeps working normally.
        See <code>AI_LOCAL_SETUP.md</code> for the full setup.
    </div>
    @elseif($report['status'] !== 'Healthy')
    <div class="alert-danger">
        <strong>Local AI is not fully ready.</strong>
        {{ (app(\App\Services\AI\AIHealthService::class))->hint($report) }}
        <a href="{{ route('settings.ai') }}">View AI status</a>.
    </div>
    @endif

    <div style="display:grid; grid-template-columns: minmax(220px, 280px) 1fr; gap:1rem; align-items:start;">
        <!-- Conversation list -->
        <div class="card" style="padding:1rem;">
            <a href="{{ route('ai.assistant') }}" class="btn-primary btn-sm" style="display:block; text-align:center; margin-bottom:0.75rem;">
                + New conversation
            </a>

            <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:0.5rem;">
                Your conversations
            </div>

            @forelse($conversations as $conversation)
            <div style="display:flex; align-items:center; gap:0.25rem; margin-bottom:0.25rem;">
                <a href="{{ route('ai.assistant', ['conversation' => $conversation->id]) }}"
                    class="submenu-link {{ $active && $active->id === $conversation->id ? 'active' : '' }}"
                    style="flex:1; display:block; padding:0.4rem 0.5rem; border-radius:0.375rem; text-decoration:none; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    {{ $conversation->displayTitle() }}
                </a>
                <button type="button"
                    class="btn-secondary btn-sm js-delete-conversation"
                    data-url="{{ route('ai.conversations.destroy', $conversation) }}"
                    title="Delete conversation"
                    style="padding:0.25rem 0.4rem;">&times;</button>
            </div>
            @empty
            <p style="font-size:0.8rem; color:var(--text-muted);">No conversations yet. Ask a question to begin.</p>
            @endforelse
        </div>

        <!-- Chat panel -->
        <div class="card" style="padding:1rem;">
            <div id="chatWindow"
                data-ask-url="{{ route('ai.assistant.ask') }}"
                style="min-height:280px; max-height:52vh; overflow-y:auto; padding:0.75rem; border:1px solid var(--border-color); border-radius:0.5rem; background:var(--bg-subtle, #f8fafc);">
                @if($messages->isEmpty())
                <p style="color:var(--text-muted); font-size:0.9rem;">
                    Start by asking a question. Try one of the suggestions below.
                </p>
                @else
                @foreach($messages as $message)
                @php $isUser = $message->role === 'user'; @endphp
                <div style="margin-bottom:0.75rem; text-align:{{ $isUser ? 'right' : 'left' }};">
                    <div style="display:inline-block; max-width:85%; text-align:left; padding:0.5rem 0.75rem; border-radius:0.5rem; white-space:pre-wrap;
                                {{ $isUser ? 'background:#2563eb; color:#fff;' : 'background:#fff; border:1px solid var(--border-color);' }}">
                        {{ $message->content }}
                    </div>
                </div>
                @endforeach
                @endif
            </div>

            <form id="chatForm" style="margin-top:0.75rem;">
                @csrf
                <input type="hidden" name="conversation_id" id="conversationId" value="{{ $active?->id }}">
                <div style="display:flex; gap:0.5rem; align-items:flex-end;">
                    <textarea name="question" id="chatQuestion" class="form-control" rows="2"
                        placeholder="Ask about your activities, goals, habits, events or finances…"
                        style="flex:1; resize:vertical;"></textarea>
                    <button type="submit" class="btn-primary" id="chatSubmit">Send</button>
                </div>
            </form>

            <div style="margin-top:0.75rem;">
                <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:0.4rem;">
                    Suggestions
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:0.375rem;">
                    @foreach($suggestions as $suggestion)
                    <button type="button" class="btn-secondary btn-sm js-suggestion"
                        style="font-size:0.78rem; padding:0.3rem 0.6rem;">{{ $suggestion }}</button>
                    @endforeach
                </div>
            </div>

            <p style="font-size:0.72rem; color:var(--text-muted); margin-top:0.75rem;">
                Local AI is powered by Ollama. Model: <strong>{{ $report['llm_model'] ?: 'not selected' }}</strong> ·
                Embeddings: <strong>{{ $report['embedding_model'] ?: 'not selected' }}</strong> ·
                Vector records: <strong>{{ $report['vector']['record_count'] ?? 0 }}</strong>.
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('chatForm');
            const window_ = document.getElementById('chatWindow');
            const question = document.getElementById('chatQuestion');
            const conversationId = document.getElementById('conversationId');
            const submit = document.getElementById('chatSubmit');
            const askUrl = window_.dataset.askUrl;
            const token = document.querySelector('input[name="_token"]').value;

            function bubble(text, isUser) {
                const wrap = document.createElement('div');
                wrap.style.marginBottom = '0.75rem';
                wrap.style.textAlign = isUser ? 'right' : 'left';
                const inner = document.createElement('div');
                inner.style.display = 'inline-block';
                inner.style.maxWidth = '85%';
                inner.style.textAlign = 'left';
                inner.style.padding = '0.5rem 0.75rem';
                inner.style.borderRadius = '0.5rem';
                inner.style.whiteSpace = 'pre-wrap';
                if (isUser) {
                    inner.style.background = '#2563eb';
                    inner.style.color = '#fff';
                } else {
                    inner.style.background = '#fff';
                    inner.style.border = '1px solid var(--border-color)';
                }
                inner.textContent = text;
                wrap.appendChild(inner);
                window_.appendChild(wrap);
                window_.scrollTop = window_.scrollHeight;
                return inner;
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const text = question.value.trim();
                if (!text) return;

                bubble(text, true);
                question.value = '';
                submit.disabled = true;
                const pending = bubble('Thinking locally…', false);

                fetch(askUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                        body: JSON.stringify({
                            question: text,
                            conversation_id: conversationId.value || null,
                        }),
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return {
                                ok: res.ok,
                                data: data
                            };
                        });
                    })
                    .then(function(result) {
                        if (result.data && result.data.ok) {
                            pending.textContent = result.data.answer;
                            if (result.data.conversation_id) {
                                conversationId.value = result.data.conversation_id;
                            }
                        } else {
                            pending.textContent = (result.data && result.data.error) ?
                                result.data.error :
                                'Local AI is currently unavailable. Please start Ollama and try again.';
                            pending.style.borderColor = '#ef4444';
                        }
                    })
                    .catch(function() {
                        pending.textContent = 'Local AI is currently unavailable. Please start Ollama and try again.';
                        pending.style.borderColor = '#ef4444';
                    })
                    .finally(function() {
                        submit.disabled = false;
                        question.focus();
                    });
            });

            document.querySelectorAll('.js-suggestion').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    question.value = btn.textContent.trim();
                    question.focus();
                });
            });

            document.querySelectorAll('.js-delete-conversation').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    // Centralized confirmation modal (no native confirm()).
                    window.Alerts.confirm({
                        title: 'Delete conversation?',
                        message: 'Are you sure you want to delete this conversation? This action cannot be undone.',
                        action: 'Delete'
                    }, function() {
                        fetch(btn.dataset.url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                        }).then(function() {
                            window.location = '{{ route('ai.assistant') }}';
                        });
                    });
                });
            });
        });
    </script>
</x-app-layout>