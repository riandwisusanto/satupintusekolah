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
        Schema::table('teacher_journals', function (Blueprint $table) {
            // Try to drop the foreign key with common naming conventions
            try {
                $table->dropForeign(['subject_id']);
            } catch (\Exception $e) {
                // If that fails, try with the full foreign key name
                try {
                    $table->dropForeign('teacher_journals_subject_id_foreign');
                } catch (\Exception $e2) {
                    // Last resort - try without the table prefix
                    $table->dropForeign('subject_id_foreign');
                }
            }
            $table->dropColumn('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_journals', function (Blueprint $table) {
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
        });
    }
};
