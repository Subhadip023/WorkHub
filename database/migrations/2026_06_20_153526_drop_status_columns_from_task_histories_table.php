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
        Schema::table('task_histories', function (Blueprint $table) {
            $table->dropColumn(['old_status', 'new_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_histories', function (Blueprint $table) {
            $table->integer('old_status')->nullable()->after('user_id');
            $table->integer('new_status')->nullable()->after('old_status');
        });
    }
};
