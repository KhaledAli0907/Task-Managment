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
        Schema::table('task_dependencies', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index('task_id', 'idx_task_dependencies_task_id');
            $table->index('dependency_task_id', 'idx_task_dependencies_dependency_task_id');
            
            // Add composite index for common queries
            $table->index(['task_id', 'dependency_task_id'], 'idx_task_dependencies_composite');
            
            // Add unique constraint to prevent duplicate dependencies
            $table->unique(['task_id', 'dependency_task_id'], 'unique_task_dependency');
        });
        
        // Add index on tasks status for dependency completion checks
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('status', 'idx_tasks_status');
            $table->index(['status', 'id'], 'idx_tasks_status_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_dependencies', function (Blueprint $table) {
            $table->dropIndex('idx_task_dependencies_task_id');
            $table->dropIndex('idx_task_dependencies_dependency_task_id');
            $table->dropIndex('idx_task_dependencies_composite');
            $table->dropUnique('unique_task_dependency');
        });
        
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_status');
            $table->dropIndex('idx_tasks_status_id');
        });
    }
};