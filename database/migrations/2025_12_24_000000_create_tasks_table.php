<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('status')->default('pending');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('project_id');

            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
