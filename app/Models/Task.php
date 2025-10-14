<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'description',
        'status',
        'completed',
        'due_date',
        'assignee_id',
        'parent_task_id'
    ];

    public function setStatusAttribute(string $status): void
    {
        $validStatuses = array_column(TaskStatus::cases(), 'value');
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->attributes['status'] = $status;
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    /**
     * Get the parent task of this task
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Get all child tasks of this task
     */
    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Check if this task is a parent task
     */
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this task is a child task
     */
    public function isChild(): bool
    {
        return !is_null($this->parent_task_id);
    }

    /**
     * Check if all child tasks are completed
     */
    public function areChildrenCompleted(): bool
    {
        if (!$this->isParent()) {
            return true;
        }

        return !$this->children()
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->exists();
    }

}
