<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use App\Services\TaskDependencyService;

class ValidateDependencyBeforeAdding
{
    public function __construct(
        private TaskDependencyService $dependencyService
    ) {}

    public function handle(string $taskId, string $dependencyTaskId): Task
    {
        $task = Task::findOrFail($taskId);
        $dependencyTask = Task::findOrFail($dependencyTaskId);

        // Check for self-dependency
        if ($taskId === $dependencyTaskId) {
            throw new \InvalidArgumentException('Task cannot depend on itself');
        }

        // Check for circular dependency using optimized CTE query
        if ($this->dependencyService->wouldCreateCircularDependency($taskId, $dependencyTaskId)) {
            throw new \InvalidArgumentException('This dependency would create a circular reference');
        }

        // Check if dependency already exists using optimized query
        if ($this->dependencyAlreadyExists($taskId, $dependencyTaskId)) {
            throw new \InvalidArgumentException('Dependency already exists');
        }

        return $task;
    }

    private function dependencyAlreadyExists(string $taskId, string $dependencyTaskId): bool
    {
        return \DB::table('task_dependencies')
            ->where('task_id', $taskId)
            ->where('dependency_task_id', $dependencyTaskId)
            ->exists();
    }
}
