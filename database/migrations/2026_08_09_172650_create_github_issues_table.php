<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('github_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('project_github_repo_id')->nullable()->constrained('project_github_repos')->onDelete('set null');
            $table->unsignedBigInteger('github_issue_id')->nullable();
            $table->integer('issue_number')->nullable();
            $table->string('issue_url')->nullable();
            $table->string('title')->nullable();
            $table->string('state')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('github_issues');
    }
};
