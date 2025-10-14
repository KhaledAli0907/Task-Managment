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
        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('parent_task_id')->nullable()->after('assignee_id');
            $table->foreign('parent_task_id')->references('id')->on('tasks')->onDelete('cascade');
            
            // Add constraint to prevent self-reference
            $table->index(['id', 'parent_task_id'], 'tasks_parent_child_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_task_id']);
            $table->dropIndex('tasks_parent_child_index');
            $table->dropColumn('parent_task_id');
        });
    }
};
