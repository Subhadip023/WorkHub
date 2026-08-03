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
            $table->tinyInteger('default_type')->default(1)->after('default_priority')->comment('1: Task, 2: Bug, 3: Feature, 4: Improvement');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_task_apis', function (Blueprint $table) {
            $table->dropColumn('default_type');
        });
    }
};
