<?php

namespace App\Jobs;

use App\Models\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IndexSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300; // 5 minutes

    /** @var array<int, int> */
    public array $backoff = [60, 120, 300]; // Retry after 1min, 2min, 5min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $contentId = null,
        public bool $reindexAll = false
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->reindexAll) {
                $this->reindexAll();
            } elseif ($this->contentId) {
                $this->indexContent($this->contentId);
            } else {
                Log::channel('jobs')->warning('IndexSearchJob: No content ID or reindex flag provided');
            }
        } catch (\Exception $e) {
            Log::channel('jobs')->error('IndexSearchJob failed: '.$e->getMessage(), [
                'content_id' => $this->contentId,
                'reindex_all' => $this->reindexAll,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Re-throw to trigger retry
        }
    }

    protected function indexContent(int $contentId): void
    {
        $content = Content::findOrFail($contentId);

        // Here you would implement your search indexing logic
        // For example, using Laravel Scout, Elasticsearch, Algolia, etc.

        // For now, we'll just log it
        Log::channel('jobs')->info('IndexSearchJob: Content indexed', [
            'content_id' => $contentId,
            'title' => $content->title,
        ]);

        // Example: If using Laravel Scout
        // $content->searchable();

        // Example: If using custom search index
        // SearchService::index($content);
    }

    protected function reindexAll(): void
    {
        Log::channel('jobs')->info('IndexSearchJob: Starting full reindex');

        // Index all published content
        $contents = Content::where('status', 'published')
            ->chunk(100, function ($contents) {
                foreach ($contents as $content) {
                    $this->indexContent($content->id);
                }
            });

        Log::channel('jobs')->info('IndexSearchJob: Full reindex completed');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('jobs')->error('IndexSearchJob permanently failed', [
            'content_id' => $this->contentId,
            'reindex_all' => $this->reindexAll,
            'error' => $exception->getMessage(),
        ]);
    }
}
