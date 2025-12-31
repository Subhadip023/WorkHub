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
        Schema::create('projects', function (Blueprint $table) {
    $table->engine = 'InnoDB';
    $table->id();
    $table->unsignedBigInteger('company_id');
    $table->string('name');
    $table->text('description')->nullable();
    $table->unsignedBigInteger('created_by');
    $table->timestamps();

    $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
    $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
