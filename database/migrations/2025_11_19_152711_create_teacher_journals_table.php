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
        Schema::create('teacher_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('set null');
            $table->date('date');
            $table->string('theme');
            $table->text('activity');
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['teacher_id', 'date']);
            $table->index(['class_id', 'date']);
            $table->index(['subject_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_journals');
    }
};
