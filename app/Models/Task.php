<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Services\TaskDependencyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Uses optimized CTE query for better performance
     */
    public function getAllDependentTasks(): Collection
    {
        return app(TaskDependencyService::class)->getAllDependentTasks($this->id);
    }

    /**
     * Get all dependency tasks with their details
     * Uses optimized single query with caching
     */
    public function getDependencyTasks(): Collection
    {
        return app(TaskDependencyService::class)->getDependencyTasks($this->id);
    }

    /**
     * Check if all dependencies are completed
     * Uses optimized query with caching
     */
    public function areDependenciesCompleted(): bool
    {
        return app(TaskDependencyService::class)->areDependenciesCompleted($this->id);
    }

    /**
     * Get dependency hierarchy for this task
     */
    public function getDependencyHierarchy(): array
    {
        return app(TaskDependencyService::class)->getDependencyHierarchy($this->id);
    }

    /**
     * Clear dependency cache when task is updated
     */
    protected static function booted(): void
    {
        static::updated(function (Task $task) {
            // Clear cache when task status changes
            if ($task->isDirty('status')) {
                app(TaskDependencyService::class)->clearTaskCache($task->id);
                
                // Also clear cache for dependent tasks
                $dependentTasks = $task->getAllDependentTasks();
                foreach ($dependentTasks as $dependentTask) {
                    app(TaskDependencyService::class)->clearTaskCache($dependentTask->id);
                }
            }
        });

        static::deleted(function (Task $task) {
            app(TaskDependencyService::class)->clearTaskCache($task->id);
        });
    }
}
