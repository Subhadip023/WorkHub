<?php

namespace App\Console\Commands;

use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Support\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SanitizeStoredHtml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:sanitize-html {--dry-run : Report how many rows would change without writing}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sanitize rich-text descriptions stored before the XSS fix (tasks, notes, projects)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        foreach ([Task::class, Note::class, Project::class] as $model) {
            $table = (new $model)->getTable();
            $changed = 0;

            // Raw query builder: skips model events/activity logging and includes trashed rows.
            DB::table($table)
                ->whereNotNull('description')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $dryRun, &$changed) {
                    foreach ($rows as $row) {
                        $clean = HtmlSanitizer::clean($row->description);
                        if ($clean !== $row->description) {
                            $changed++;
                            if (! $dryRun) {
                                DB::table($table)->where('id', $row->id)->update(['description' => $clean]);
                            }
                        }
                    }
                });

            $this->info(sprintf('%s: %d row(s) %s', $table, $changed, $dryRun ? 'would change' : 'sanitized'));
        }

        return self::SUCCESS;
    }
}
