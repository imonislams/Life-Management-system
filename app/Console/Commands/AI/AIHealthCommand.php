<?php

namespace App\Console\Commands\AI;

use App\Services\AI\AIHealthService;
use Illuminate\Console\Command;

/**
 * Report the health of the 100% local AI stack.
 *
 * Checks: AI enabled, Ollama reachable, LLM model available, embedding model
 * available, vector DB reachable and collection available — then prints the
 * hardware summary used for model selection.
 */
class AIHealthCommand extends Command
{
    protected $signature = 'ai:health {--user= : Include per-user vector counts and last reindex time}';

    protected $description = 'Check the local AI stack (Ollama + Qdrant + models)';

    public function handle(AIHealthService $health): int
    {
        $userId = $this->option('user') !== null ? (int) $this->option('user') : null;

        $report = $health->report($userId);

        $this->info('Local AI health check');
        $this->newLine();

        $this->table(
            ['Check', 'Value'],
            [
                ['Status', $report['status']],
                ['AI enabled', $report['enabled'] ? 'yes' : 'no'],
                ['Provider', $report['provider']],
                ['Ollama reachable', $report['ollama']['reachable'] ? 'yes' : 'no'],
                ['Ollama URL', $report['ollama']['base_url'] ?: '(not set)'],
                ['LLM model', $report['llm_model'] ?: '(none)'],
                ['LLM model available', $report['llm_model_available'] ? 'yes' : 'no'],
                ['Embedding model', $report['embedding_model'] ?: '(none)'],
                ['Embedding model available', $report['embedding_model_available'] ? 'yes' : 'no'],
                ['Vector DB reachable', $report['vector']['reachable'] ? 'yes' : 'no'],
                ['Vector records', $report['vector']['record_count'] ?? '(n/a)'],
                ['Last reindex', $report['last_reindex'] ?? '(never)'],
            ]
        );

        if (! empty($report['ollama']['installed_models'])) {
            $this->line('Installed Ollama models: ' . implode(', ', $report['ollama']['installed_models']));
        } else {
            $this->line('Installed Ollama models: (none detected)');
        }

        if (! empty($report['hardware'])) {
            $this->line(sprintf(
                'Hardware: RAM %s GB, CPU %s, GPU %s, Disk free %s GB',
                $report['hardware']['ram_gb'] ?? '?',
                $report['hardware']['cpu'] ?? '?',
                $report['hardware']['gpu'] ?? 'none',
                $report['hardware']['disk_free_gb'] ?? '?'
            ));
        }

        $this->newLine();
        $this->line($health->hint($report));

        return $report['status'] === 'Unavailable' ? self::FAILURE : self::SUCCESS;
    }
}
