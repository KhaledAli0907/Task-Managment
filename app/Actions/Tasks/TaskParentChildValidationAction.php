<?php

namespace App\Actions\Tasks;

use App\Models\Task;

class TaskParentChildValidationAction
{
    public function handle(Task $task): void
    {
        $this->validateSelfReference($task);
        $this->validateNestingDepth($task);
    }

    private function validateSelfReference(Task $task): void
    {
        // Prevent self-reference
        if ($task->parent_task_id === $task->id) {
            throw new \InvalidArgumentException('Task cannot be its own parent');
        }
    }

    private function validateNestingDepth(Task $task): void
    {
        // Prevent children from having their own children (no nesting)
        if ($task->parent_task_id && $task->children()->exists()) {
            throw new \InvalidArgumentException('Child tasks cannot have their own children');
        }
    }
}
