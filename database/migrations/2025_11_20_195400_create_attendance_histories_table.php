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
        Schema::create('attendance_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_attendance_id')->constrained('teacher_attendances')->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->enum('action', ['created', 'checked_in', 'checked_out', 'status_updated', 'break_started', 'break_ended', 'photo_uploaded']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['teacher_attendance_id', 'created_at']);
            $table->index('changed_by');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_histories');
    }
};
