<?php

namespace App\Console\Commands\AI;

use App\Services\AI\IndexingService;
use Illuminate\Console\Command;

/**
 * Rebuild the semantic vector index from MySQL (the authoritative source).
 *
 * Usage:
 *   php artisan ai:reindex              # every user
 *   php artisan ai:reindex --user=1     # a single user
 *
 * The command reads authoritative MySQL data, generates LOCAL embeddings, stores
 * the vectors with ownership metadata, reports progress and handles per-record
 * failures gracefully (a bad row never aborts the whole rebuild).
 */
class ReindexCommand extends Command
{
    protected $signature = 'ai:reindex {--user= : Reindex only this user id} {--force : Run even when AI_ENABLED is false}';

    protected $description = 'Rebuild the local AI semantic index from MySQL';

    public function handle(IndexingService $indexing): int
    {
        if (! config('ai.enabled') && ! $this->option('force')) {
            $this->warn('The AI layer is disabled (AI_ENABLED=false). Re-run with --force to index anyway.');
            $this->line('Tip: enable it with AI_ENABLED=true and ensure Ollama is running.');

            return self::FAILURE;
        }

        if (! config('ai.enabled') && $this->option('force')) {
            // --force still cannot embed without an enabled privacy gate, so tell
            // the operator precisely what to flip.
            $this->warn('AI_ENABLED is false: indexing is skipped unless AI_ENABLED=true.');
        }

        $userId = $this->option('user') !== null ? (int) $this->option('user') : null;

        $this->info('Rebuilding the local AI index from MySQL…');
        $this->line($userId ? "Scope: user #{$userId}" : 'Scope: all users');

        if (! app(\App\Services\AI\Contracts\EmbeddingServiceInterface::class)->isAvailable()) {
            $this->error('The local embedding runtime (Ollama) is not reachable.');
            $this->line('Start Ollama and pull an embedding model, e.g.  ollama pull nomic-embed-text');

            return self::FAILURE;
        }

        $result = $indexing->reindex($userId);

        $this->newLine();

        $this->info(sprintf(
            'Done. Indexed %d records across %d user(s). Skipped %d user(s) (indexing not permitted).',
            $result['indexed'],
            $result['users'],
            $result['skipped']
        ));

        return self::SUCCESS;
    }
}
