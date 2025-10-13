<?php

namespace App\Actions\Tasks;

use App\Models\Task;

class ValidateDependencyBeforeAdding
{
    public function handle(string $taskId, string $dependencyTaskId): Task
    {
        $task = Task::findOrFail($taskId);
        $dependencyTask = Task::findOrFail($dependencyTaskId);

        // Check for circular dependency
        if ($this->wouldCreateCircularDependency($taskId, $dependencyTaskId)) {
            throw new \InvalidArgumentException('This dependency would create a circular reference');
        }

        // Check if dependency already exists
        if ($task->dependencies()->where('dependency_task_id', $dependencyTaskId)->exists()) {
            throw new \InvalidArgumentException('Dependency already exists');
        }
        return $task;
    }

    private function wouldCreateCircularDependency(string $taskId, string $dependencyTaskId): bool
    {
        // Get all tasks that depend on the current task (recursively)
        $dependentTasks = $this->getAllDependentTaskIds($taskId);

        // If the dependency task is in the list of dependent tasks, it would create a cycle
        return in_array($dependencyTaskId, $dependentTasks);
    }

    private function getAllDependentTaskIds(string $taskId): array
    {
        $dependentTaskIds = [];
        $directDependents = Task::whereHas('dependencies', function ($query) use ($taskId) {
            $query->where('dependency_task_id', $taskId);
        })->pluck('id')->toArray();

        foreach ($directDependents as $dependentId) {
            $dependentTaskIds[] = $dependentId;
            // Recursively get all dependents
            $dependentTaskIds = array_merge($dependentTaskIds, $this->getAllDependentTaskIds($dependentId));
        }

        return array_unique($dependentTaskIds);
    }
}
