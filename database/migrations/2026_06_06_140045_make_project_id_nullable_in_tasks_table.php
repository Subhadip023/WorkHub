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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop foreign key first if it exists
            $table->dropForeign(['project_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Re-create nullable project_id and add user_id
            $table->foreignId('project_id')->nullable()->change();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->after('project_id')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['project_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            // Re-create non-nullable project_id
            $table->foreignId('project_id')->nullable(false)->change();
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }
};
