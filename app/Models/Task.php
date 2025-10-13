<?php

namespace App\Models;

use App\Enums\TaskStatus;
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
     */
    public function getAllDependentTasks(): Collection
    {
        $dependentTasks = collect();

        // Find all direct dependents
        $directDependents = Task::whereHas('dependencies', function ($query) {
            $query->where('dependency_task_id', $this->id);
        })->get();

        foreach ($directDependents as $dependent) {
            $dependentTasks->push($dependent);
            // Recursively get all dependents
            $dependentTasks = $dependentTasks->merge($dependent->getAllDependentTasks());
        }

        return new Collection($dependentTasks->unique('id')->values());
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
     */
    public function areDependenciesCompleted(): bool
    {
        return !$this->dependencies()
            ->whereHas('dependencyTask', function ($query) {
                $query->where('status', '!=', TaskStatus::COMPLETED->value);
            })
            ->exists();
    }
}
