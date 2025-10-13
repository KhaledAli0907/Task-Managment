<?php

namespace App\Actions\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

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
        // Get all tasks that depend on the current task (recursively) using a single query
        $dependentTasks = $this->getAllDependentTaskIds($taskId);

        // If the dependency task is in the list of dependent tasks, it would create a cycle
        return in_array($dependencyTaskId, $dependentTasks);
    }

    /**
     * Get all dependent task IDs using a recursive CTE for optimal performance
     * This replaces multiple recursive queries with a single efficient query
     */
    private function getAllDependentTaskIds(string $taskId): array
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
        ", [$taskId]);

        return array_map(fn($row) => $row->task_id, $results);
    }
}
