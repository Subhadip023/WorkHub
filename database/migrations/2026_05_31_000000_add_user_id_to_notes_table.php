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
        if (!Schema::hasColumn('notes', 'user_id')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('notes', 'user_id')) {
            Schema::table('notes', function (Blueprint $table) {
                // Drop foreign key first depending on db support
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Fail-safe
                }
                $table->dropColumn('user_id');
            });
        }
    }
};
