<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_dependencies', function (Blueprint $table) {
            // Add indexes for foreign key columns to improve join performance
            $table->index('task_id');
            $table->index('dependency_task_id');
            
            // Add composite index for common query patterns
            $table->index(['task_id', 'dependency_task_id']);
            
            // Add unique constraint to prevent duplicate dependencies
            $table->unique(['task_id', 'dependency_task_id'], 'unique_task_dependency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_dependencies', function (Blueprint $table) {
            $table->dropIndex(['task_id']);
            $table->dropIndex(['dependency_task_id']);
            $table->dropIndex(['task_id', 'dependency_task_id']);
            $table->dropUnique('unique_task_dependency');
        });
    }
};
