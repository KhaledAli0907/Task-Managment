<?php

namespace App\Actions\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;

class TaskStatusValidationAction
{
    public function handle(Task $task, string $status): void
    {
        $this->validateTaskExists($task);
        $this->validateUserCanUpdateTask($task);
        $this->validateTaskCompletionRules($task, $status);
    }

    private function validateTaskExists(Task $task): void
    {
        if (!$task) {
            throw new \InvalidArgumentException('Task not found');
        }
    }

    private function validateUserCanUpdateTask(Task $task): void
    {
        // Check if user can update this task
        if (auth()->user()->isUser() && $task->assignee_id !== auth()->id()) {
            throw new \Exception('You can only update tasks assigned to you');
        }
    }

    private function validateTaskCompletionRules(Task $task, string $status): void
    {
        // Prevent completion if children are not completed
        if ($status === TaskStatus::COMPLETED->value && !$task->areChildrenCompleted()) {
            throw new \Exception('Cannot complete task: child tasks not completed');
        }
    }
}
