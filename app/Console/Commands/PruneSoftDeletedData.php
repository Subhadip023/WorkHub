<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\CompanyUsers;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Console\Command;

class PruneSoftDeletedData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trash:prune {--days=0 : Prune data deleted more than this many days ago (0 to prune all)}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Permanently delete all soft-deleted tasks, projects, companies, and memberships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        $this->info('Starting prune process...'.($days > 0 ? " (deletes older than {$days} days)" : ' (deletes ALL soft-deleted data)'));

        // Build base queries
        $taskQuery = Task::onlyTrashed();
        $projectQuery = Project::onlyTrashed();
        $companyQuery = Company::onlyTrashed();
        $companyUserQuery = CompanyUsers::onlyTrashed();

        if ($days > 0) {
            $cutoff = now()->subDays($days);
            $taskQuery->where('deleted_at', '<=', $cutoff);
            $projectQuery->where('deleted_at', '<=', $cutoff);
            $companyQuery->where('deleted_at', '<=', $cutoff);
            $companyUserQuery->where('deleted_at', '<=', $cutoff);
        }

        // Count items to delete
        $tasksCount = $taskQuery->count();
        $projectsCount = $projectQuery->count();
        $companiesCount = $companyQuery->count();
        $companyUsersCount = $companyUserQuery->count();

        // 1. Prune Tasks
        if ($tasksCount > 0) {
            $taskQuery->forceDelete();
            $this->line("Pruned {$tasksCount} tasks.");
        } else {
            $this->line('No tasks to prune.');
        }

        // 2. Prune Projects
        if ($projectsCount > 0) {
            $projectQuery->forceDelete();
            $this->line("Pruned {$projectsCount} projects.");
        } else {
            $this->line('No projects to prune.');
        }

        // 3. Prune Companies
        if ($companiesCount > 0) {
            $companyQuery->forceDelete();
            $this->line("Pruned {$companiesCount} companies.");
        } else {
            $this->line('No companies to prune.');
        }

        // 4. Prune Company Users
        if ($companyUsersCount > 0) {
            $companyUserQuery->forceDelete();
            $this->line("Pruned {$companyUsersCount} company memberships.");
        } else {
            $this->line('No company memberships to prune.');
        }

        $totalPruned = $tasksCount + $projectsCount + $companiesCount + $companyUsersCount;
        $this->info("Pruning complete! Total {$totalPruned} records permanently removed.");

        return self::SUCCESS;
    }
}
