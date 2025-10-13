<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'status',
        'completed',
        'due_date',
        'assignee_id'
    ];

    public function setStatusAttribute(string $status): void
    {
        $validStatuses = array_column(TaskStatus::cases(), 'value');
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->attributes['status'] = $status;
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get dependencies for this task (tasks that must be completed before this one)
     */
    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    /**
     * Get tasks that depend on this task
     */
    public function dependents()
    {
        return $this->hasMany(TaskDependency::class, 'dependency_task_id');
    }

    /**
     * Get all dependent tasks recursively (tasks that depend on this task)
     * Optimized to use a single recursive CTE query instead of N+1 queries
     */
    public function getAllDependentTasks(): Collection
    {
        // Use recursive CTE to get all dependent tasks in a single query
        $results = DB::select("
            WITH RECURSIVE dependent_tasks AS (
                -- Base case: direct dependents
                SELECT task_id
                FROM task_dependencies
                WHERE dependency_task_id = ?
                
                UNION
                
                -- Recursive case: dependents of dependents
                SELECT td.task_id
                FROM task_dependencies td
                INNER JOIN dependent_tasks dt ON td.dependency_task_id = dt.task_id
            )
            SELECT DISTINCT task_id FROM dependent_tasks
        ", [$this->id]);

        $taskIds = array_map(fn($row) => $row->task_id, $results);

        if (empty($taskIds)) {
            return new Collection([]);
        }

        // Fetch all tasks in a single query
        return Task::whereIn('id', $taskIds)->get();
    }

    /**
     * Get all dependency tasks with their details
     */
    public function getDependencyTasks()
    {
        return $this->dependencies()
            ->with('dependencyTask')
            ->get()
            ->map(fn($dep) => $dep->dependencyTask);
    }

    /**
     * Check if all dependencies are completed
     * Optimized to use a single join query instead of subqueries
     */
    public function areDependenciesCompleted(): bool
    {
        // Use a single query with a join to check if any incomplete dependencies exist
        $incompleteCount = DB::table('task_dependencies')
            ->join('tasks', 'task_dependencies.dependency_task_id', '=', 'tasks.id')
            ->where('task_dependencies.task_id', $this->id)
            ->where('tasks.status', '!=', TaskStatus::COMPLETED->value)
            ->count();

        return $incompleteCount === 0;
    }
}
