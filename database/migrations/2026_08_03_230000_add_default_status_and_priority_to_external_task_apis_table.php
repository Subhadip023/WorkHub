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
        Schema::table('external_task_apis', function (Blueprint $table) {
            $table->tinyInteger('default_status')->default(1)->after('assigned_user_id')->comment('1: Todo, 2: In Progress, 3: Completed, 4: On Hold');
            $table->tinyInteger('default_priority')->default(2)->after('default_status')->comment('1: Low, 2: Medium, 3: High, 4: Urgent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_task_apis', function (Blueprint $table) {
            $table->dropColumn(['default_status', 'default_priority']);
        });
    }
};
