<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('theme')->comment('1: Todo, 2: In Progress, 3: Completed, 4: On Hold');
            $table->tinyInteger('priority')->default(2)->after('status')->comment('1: Low, 2: Medium, 3: High, 4: Urgent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['status', 'priority']);
        });
    }
};
