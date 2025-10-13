<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskDependencyService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'task_dependencies:';

    /**
     * Get all dependent tasks recursively using optimized CTE query
     */
    public function getAllDependentTasks(string $taskId): Collection
    {
        $cacheKey = self::CACHE_PREFIX . "dependents:{$taskId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId) {
            // Use Common Table Expression (CTE) for efficient recursive query
            $sql = "
                WITH RECURSIVE dependent_tasks AS (
                    -- Base case: direct dependents
                    SELECT t.id, t.title, t.status, 1 as depth
                    FROM tasks t
                    INNER JOIN task_dependencies td ON t.id = td.task_id
                    WHERE td.dependency_task_id = ?
                    
                    UNION ALL
                    
                    -- Recursive case: dependents of dependents
                    SELECT t.id, t.title, t.status, dt.depth + 1
                    FROM tasks t
                    INNER JOIN task_dependencies td ON t.id = td.task_id
                    INNER JOIN dependent_tasks dt ON td.dependency_task_id = dt.id
                    WHERE dt.depth < 10 -- Prevent infinite recursion
                )
                SELECT DISTINCT id, title, status, MIN(depth) as min_depth
                FROM dependent_tasks
                GROUP BY id, title, status
                ORDER BY min_depth, title
            ";

            $results = DB::select($sql, [$taskId]);
            
            // Convert to Task models
            $taskIds = collect($results)->pluck('id');
            return Task::whereIn('id', $taskIds)
                ->orderByRaw("FIELD(id, '" . implode("','", $taskIds->toArray()) . "')")
                ->get();
        });
    }

    /**
     * Get all dependency tasks efficiently with single query
     */
    public function getDependencyTasks(string $taskId): Collection
    {
        $cacheKey = self::CACHE_PREFIX . "dependencies:{$taskId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId) {
            return Task::select('tasks.*')
                ->join('task_dependencies', 'tasks.id', '=', 'task_dependencies.dependency_task_id')
                ->where('task_dependencies.task_id', $taskId)
                ->orderBy('tasks.title')
                ->get();
        });
    }

    /**
     * Check if all dependencies are completed using optimized query
     */
    public function areDependenciesCompleted(string $taskId): bool
    {
        $cacheKey = self::CACHE_PREFIX . "completed:{$taskId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId) {
            // Count incomplete dependencies in single query
            $incompleteCount = DB::table('task_dependencies')
                ->join('tasks', 'task_dependencies.dependency_task_id', '=', 'tasks.id')
                ->where('task_dependencies.task_id', $taskId)
                ->where('tasks.status', '!=', 'completed')
                ->count();
                
            return $incompleteCount === 0;
        });
    }

    /**
     * Optimized circular dependency check using CTE
     */
    public function wouldCreateCircularDependency(string $taskId, string $dependencyTaskId): bool
    {
        // Use CTE to find all tasks that the dependency task depends on
        $sql = "
            WITH RECURSIVE task_dependencies_recursive AS (
                -- Base case: direct dependencies of the dependency task
                SELECT dependency_task_id as dependent_id, 1 as depth
                FROM task_dependencies
                WHERE task_id = ?
                
                UNION ALL
                
                -- Recursive case: dependencies of dependencies
                SELECT td.dependency_task_id, tdr.depth + 1
                FROM task_dependencies td
                INNER JOIN task_dependencies_recursive tdr ON td.task_id = tdr.dependent_id
                WHERE tdr.depth < 10 -- Prevent infinite recursion
            )
            SELECT COUNT(*) as count
            FROM task_dependencies_recursive
            WHERE dependent_id = ?
        ";

        $result = DB::select($sql, [$dependencyTaskId, $taskId]);
        return $result[0]->count > 0;
    }

    /**
     * Get dependency hierarchy for a task (breadth-first traversal)
     */
    public function getDependencyHierarchy(string $taskId): array
    {
        $cacheKey = self::CACHE_PREFIX . "hierarchy:{$taskId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId) {
            $sql = "
                WITH RECURSIVE dependency_hierarchy AS (
                    -- Base case: the task itself
                    SELECT id, title, status, 0 as level, CAST(id AS CHAR(1000)) as path
                    FROM tasks
                    WHERE id = ?
                    
                    UNION ALL
                    
                    -- Recursive case: dependencies
                    SELECT t.id, t.title, t.status, dh.level + 1, 
                           CONCAT(dh.path, '->', t.id) as path
                    FROM tasks t
                    INNER JOIN task_dependencies td ON t.id = td.dependency_task_id
                    INNER JOIN dependency_hierarchy dh ON td.task_id = dh.id
                    WHERE dh.level < 10 -- Prevent infinite recursion
                )
                SELECT id, title, status, level, path
                FROM dependency_hierarchy
                ORDER BY level, title
            ";

            return DB::select($sql, [$taskId]);
        });
    }

    /**
     * Batch check dependencies completion for multiple tasks
     */
    public function batchCheckDependenciesCompleted(array $taskIds): array
    {
        if (empty($taskIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($taskIds) - 1) . '?';
        
        $sql = "
            SELECT 
                td.task_id,
                COUNT(*) as total_dependencies,
                SUM(CASE WHEN t.status = 'completed' THEN 1 ELSE 0 END) as completed_dependencies
            FROM task_dependencies td
            INNER JOIN tasks t ON td.dependency_task_id = t.id
            WHERE td.task_id IN ({$placeholders})
            GROUP BY td.task_id
        ";

        $results = DB::select($sql, $taskIds);
        
        $completionStatus = [];
        foreach ($results as $result) {
            $completionStatus[$result->task_id] = $result->total_dependencies === $result->completed_dependencies;
        }

        // Tasks with no dependencies are considered complete
        foreach ($taskIds as $taskId) {
            if (!isset($completionStatus[$taskId])) {
                $completionStatus[$taskId] = true;
            }
        }

        return $completionStatus;
    }

    /**
     * Clear cache for a specific task
     */
    public function clearTaskCache(string $taskId): void
    {
        $patterns = [
            self::CACHE_PREFIX . "dependents:{$taskId}",
            self::CACHE_PREFIX . "dependencies:{$taskId}",
            self::CACHE_PREFIX . "completed:{$taskId}",
            self::CACHE_PREFIX . "hierarchy:{$taskId}",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Clear all dependency caches (use after bulk operations)
     */
    public function clearAllDependencyCache(): void
    {
        // In production, you might want to use cache tags for more efficient clearing
        Cache::flush(); // This is a simple approach, consider using cache tags in production
    }

    /**
     * Get dependency statistics for performance monitoring
     */
    public function getDependencyStats(): array
    {
        $stats = DB::select("
            SELECT 
                COUNT(DISTINCT task_id) as tasks_with_dependencies,
                COUNT(*) as total_dependencies,
                AVG(dependency_count) as avg_dependencies_per_task,
                MAX(dependency_count) as max_dependencies_per_task
            FROM (
                SELECT task_id, COUNT(*) as dependency_count
                FROM task_dependencies
                GROUP BY task_id
            ) as task_dep_counts
        ");

        $circularChecks = DB::select("
            SELECT COUNT(*) as potential_circular_dependencies
            FROM task_dependencies td1
            INNER JOIN task_dependencies td2 ON td1.dependency_task_id = td2.task_id
            WHERE td2.dependency_task_id = td1.task_id
        ");

        return [
            'tasks_with_dependencies' => $stats[0]->tasks_with_dependencies ?? 0,
            'total_dependencies' => $stats[0]->total_dependencies ?? 0,
            'avg_dependencies_per_task' => round($stats[0]->avg_dependencies_per_task ?? 0, 2),
            'max_dependencies_per_task' => $stats[0]->max_dependencies_per_task ?? 0,
            'potential_circular_dependencies' => $circularChecks[0]->potential_circular_dependencies ?? 0,
        ];
    }
}