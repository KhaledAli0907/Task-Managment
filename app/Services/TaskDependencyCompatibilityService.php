<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TaskDependencyCompatibilityService
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'task_dependencies:';

    /**
     * Check if database supports CTEs
     */
    public function supportsCTE(): bool
    {
        try {
            // Test CTE support with a simple query
            $result = DB::select("
                WITH test_cte AS (SELECT 1 as test_col)
                SELECT test_col FROM test_cte
            ");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get database version info
     */
    public function getDatabaseInfo(): array
    {
        $version = DB::select("SELECT VERSION() as version")[0]->version;
        $isMariaDB = stripos($version, 'mariadb') !== false;
        
        if ($isMariaDB) {
            preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            $major = (int)($matches[1] ?? 0);
            $minor = (int)($matches[2] ?? 0);
            $patch = (int)($matches[3] ?? 0);
            
            return [
                'type' => 'MariaDB',
                'version' => $version,
                'major' => $major,
                'minor' => $minor,
                'patch' => $patch,
                'supports_cte' => $major > 10 || ($major == 10 && $minor >= 2),
                'supports_window_functions' => $major > 10 || ($major == 10 && $minor >= 2),
            ];
        } else {
            preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $matches);
            $major = (int)($matches[1] ?? 0);
            $minor = (int)($matches[2] ?? 0);
            $patch = (int)($matches[3] ?? 0);
            
            return [
                'type' => 'MySQL',
                'version' => $version,
                'major' => $major,
                'minor' => $minor,
                'patch' => $patch,
                'supports_cte' => $major >= 8,
                'supports_window_functions' => $major >= 8,
            ];
        }
    }

    /**
     * Get all dependent tasks - fallback version for older databases
     */
    public function getAllDependentTasksFallback(string $taskId): Collection
    {
        $cacheKey = self::CACHE_PREFIX . "dependents_fallback:{$taskId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId) {
            return $this->getDependentTasksRecursive($taskId, [], 0);
        });
    }

    /**
     * Recursive method to get dependent tasks (fallback for non-CTE databases)
     */
    private function getDependentTasksRecursive(string $taskId, array $visited = [], int $depth = 0): Collection
    {
        // Prevent infinite recursion
        if ($depth > 10 || in_array($taskId, $visited)) {
            return collect();
        }

        $visited[] = $taskId;
        $dependentTasks = collect();

        // Get direct dependents with single query
        $directDependents = Task::select('tasks.*')
            ->join('task_dependencies', 'tasks.id', '=', 'task_dependencies.task_id')
            ->where('task_dependencies.dependency_task_id', $taskId)
            ->get();

        foreach ($directDependents as $dependent) {
            $dependentTasks->push($dependent);
            
            // Get recursive dependents
            $recursiveDependents = $this->getDependentTasksRecursive(
                $dependent->id, 
                $visited, 
                $depth + 1
            );
            $dependentTasks = $dependentTasks->merge($recursiveDependents);
        }

        return $dependentTasks->unique('id');
    }

    /**
     * Optimized circular dependency check for non-CTE databases
     */
    public function wouldCreateCircularDependencyFallback(string $taskId, string $dependencyTaskId): bool
    {
        // Use iterative approach instead of recursive CTE
        $visited = [];
        $queue = [$dependencyTaskId];
        
        while (!empty($queue)) {
            $currentTaskId = array_shift($queue);
            
            if ($currentTaskId === $taskId) {
                return true; // Found cycle
            }
            
            if (in_array($currentTaskId, $visited)) {
                continue; // Already processed
            }
            
            $visited[] = $currentTaskId;
            
            // Get dependencies of current task
            $dependencies = DB::table('task_dependencies')
                ->where('task_id', $currentTaskId)
                ->pluck('dependency_task_id')
                ->toArray();
            
            $queue = array_merge($queue, $dependencies);
            
            // Safety check to prevent infinite loops
            if (count($visited) > 1000) {
                break;
            }
        }
        
        return false;
    }

    /**
     * Get dependency hierarchy using iterative approach
     */
    public function getDependencyHierarchyFallback(string $taskId): array
    {
        $cacheKey = self::CACHE_PREFIX . "hierarchy_fallback:{$taskId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($taskId) {
            $hierarchy = [];
            $queue = [['id' => $taskId, 'level' => 0, 'path' => $taskId]];
            $visited = [];
            
            while (!empty($queue)) {
                $current = array_shift($queue);
                $currentId = $current['id'];
                $level = $current['level'];
                $path = $current['path'];
                
                if (in_array($currentId, $visited) || $level > 10) {
                    continue;
                }
                
                $visited[] = $currentId;
                
                // Get task details
                $task = Task::find($currentId);
                if ($task) {
                    $hierarchy[] = (object)[
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'level' => $level,
                        'path' => $path
                    ];
                }
                
                // Get dependencies
                $dependencies = DB::table('task_dependencies')
                    ->join('tasks', 'task_dependencies.dependency_task_id', '=', 'tasks.id')
                    ->where('task_dependencies.task_id', $currentId)
                    ->select('tasks.id', 'tasks.title')
                    ->get();
                
                foreach ($dependencies as $dep) {
                    $queue[] = [
                        'id' => $dep->id,
                        'level' => $level + 1,
                        'path' => $path . '->' . $dep->id
                    ];
                }
            }
            
            return $hierarchy;
        });
    }

    /**
     * Performance comparison between CTE and fallback methods
     */
    public function benchmarkMethods(string $taskId): array
    {
        $results = [];
        
        // Test CTE method if supported
        if ($this->supportsCTE()) {
            $start = microtime(true);
            try {
                app(TaskDependencyService::class)->getAllDependentTasks($taskId);
                $results['cte_method'] = (microtime(true) - $start) * 1000;
            } catch (\Exception $e) {
                $results['cte_method'] = 'Error: ' . $e->getMessage();
            }
        }
        
        // Test fallback method
        $start = microtime(true);
        $this->getAllDependentTasksFallback($taskId);
        $results['fallback_method'] = (microtime(true) - $start) * 1000;
        
        return $results;
    }
}