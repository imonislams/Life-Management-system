@php
$badgeClass = fn (string $status): string => match ($status) {
'Healthy' => 'badge-income',
'Warning' => 'badge-active',
default => 'badge-expense',
};
$yes = fn ($v) => $v ? 'Yes' : 'No';
@endphp
<x-app-layout>
    <x-slot name="title">AI Status - Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings · AI Status</x-slot>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">AI Status</h2>
                <p class="card-subtitle">
                    Your AI assistant runs 100% locally. No paid AI API is required and no personal data leaves this machine.
                </p>
            </div>
            <span class="badge {{ $badgeClass($report['status']) }}">{{ $report['status'] }}</span>
        </div>
    </div>

    @include('settings.partials.nav')

    <div class="alert-success" style="background:#eff6ff; border-color:#bfdbfe; color:#1e3a8a;">
        {{ $hint }}
    </div>

    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">AI Status</div>
            <div class="summary-card-value" style="font-size:1.125rem;">{{ $report['status'] }}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $report['enabled'] ? 'Enabled' : 'Disabled (AI_ENABLED)' }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Ollama Status</div>
            <div class="summary-card-value" style="font-size:1.125rem;">{{ $report['ollama']['reachable'] ? 'Reachable' : 'Offline' }}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">{{ $report['ollama']['base_url'] ?: 'not configured' }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">LLM Model</div>
            <div class="summary-card-value" style="font-size:1rem;">{{ $report['llm_model'] ?: '—' }}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">Available: {{ $yes($report['llm_model_available']) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Embedding Model</div>
            <div class="summary-card-value" style="font-size:1rem;">{{ $report['embedding_model'] ?: '—' }}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">Available: {{ $yes($report['embedding_model_available']) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Vector Database</div>
            <div class="summary-card-value" style="font-size:1rem;">
                {{ ucfirst($report['vector']['describe']['driver'] ?? 'database') }}
            </div>
            <div style="font-size:0.75rem; color:var(--text-muted);">
                {{ $report['vector']['reachable'] ? 'Reachable' : 'Offline' }}
                @if(!empty($report['vector']['describe']['collection'])) · {{ $report['vector']['describe']['collection'] }} @endif
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Vector Record Count</div>
            <div class="summary-card-value">{{ $report['vector']['record_count'] ?? 0 }}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">indexed records for you</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Last Reindex</div>
            <div class="summary-card-value" style="font-size:1rem;">{{ $report['last_reindex'] ?? 'Never' }}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">php artisan ai:reindex</div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title" style="margin-bottom:0.75rem;">Installed Local Models</h3>
        @if(!empty($report['ollama']['installed_models']))
        <div style="display:flex; flex-wrap:wrap; gap:0.375rem;">
            @foreach($report['ollama']['installed_models'] as $model)
            <span class="badge badge-active">{{ $model }}</span>
            @endforeach
        </div>
        @else
        <p class="card-subtitle">No models detected. Pull one with <code>ollama pull llama3.2:3b</code> and an embedding model with <code>ollama pull nomic-embed-text</code>.</p>
        @endif
    </div>

    @if(!empty($report['hardware']))
    <div class="card">
        <h3 class="card-title" style="margin-bottom:0.75rem;">Detected Hardware</h3>
        <div class="data-table-wrapper" style="overflow-x:auto;">
            <table class="data-table" style="width:100%;">
                <tbody>
                    <tr>
                        <th style="text-align:left;">RAM</th>
                        <td>{{ $report['hardware']['ram_gb'] ?? 'unknown' }} GB</td>
                    </tr>
                    <tr>
                        <th style="text-align:left;">CPU</th>
                        <td>{{ $report['hardware']['cpu'] ?? 'unknown' }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left;">GPU</th>
                        <td>{{ $report['hardware']['gpu'] ?? 'none detected' }}</td>
                    </tr>
                    <tr>
                        <th style="text-align:left;">Disk free</th>
                        <td>{{ $report['hardware']['disk_free_gb'] ?? 'unknown' }} GB</td>
                    </tr>
                    <tr>
                        <th style="text-align:left;">OS</th>
                        <td>{{ $report['hardware']['os'] ?? PHP_OS_FAMILY }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="card-subtitle" style="margin-top:0.5rem;">
            The local model is chosen to fit this hardware automatically. You can always override it with <code>OLLAMA_MODEL</code> in your <code>.env</code>.
        </p>
    </div>
    @endif

    <div class="card">
        <h3 class="card-title" style="margin-bottom:0.75rem;">Setup Checklist</h3>
        <ol style="margin:0; padding-left:1.25rem; font-size:0.85rem; line-height:1.8;">
            <li>Install Ollama and run it: <code>ollama --version</code></li>
            <li>Pull a model: <code>ollama pull llama3.2:3b</code></li>
            <li>Pull an embedding model: <code>ollama pull nomic-embed-text</code></li>
            <li>Run Qdrant locally (Docker) and confirm it is reachable</li>
            <li>Set <code>AI_ENABLED=true</code> in <code>.env</code></li>
            <li>Build the index: <code>php artisan ai:reindex</code></li>
            <li>Verify: <code>php artisan ai:health</code></li>
        </ol>
        <p class="card-subtitle" style="margin-top:0.5rem;">Full details in <code>AI_LOCAL_SETUP.md</code>.</p>
    </div>
</x-app-layout>