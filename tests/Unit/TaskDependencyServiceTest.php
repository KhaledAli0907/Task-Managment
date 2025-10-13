<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\TaskDependency;
use App\Services\TaskDependencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TaskDependencyServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskDependencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TaskDependencyService::class);
        Cache::flush(); // Clear cache for each test
    }

    public function test_get_all_dependent_tasks_uses_cte_query()
    {
        // Create a dependency chain: A -> B -> C -> D
        $taskA = Task::factory()->create(['title' => 'Task A']);
        $taskB = Task::factory()->create(['title' => 'Task B']);
        $taskC = Task::factory()->create(['title' => 'Task C']);
        $taskD = Task::factory()->create(['title' => 'Task D']);

        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskA->id]);
        TaskDependency::create(['task_id' => $taskC->id, 'dependency_task_id' => $taskB->id]);
        TaskDependency::create(['task_id' => $taskD->id, 'dependency_task_id' => $taskC->id]);

        $dependentTasks = $this->service->getAllDependentTasks($taskA->id);

        $this->assertCount(3, $dependentTasks);
        $this->assertTrue($dependentTasks->contains('id', $taskB->id));
        $this->assertTrue($dependentTasks->contains('id', $taskC->id));
        $this->assertTrue($dependentTasks->contains('id', $taskD->id));
    }

    public function test_get_dependency_tasks_returns_correct_tasks()
    {
        $task = Task::factory()->create();
        $dep1 = Task::factory()->create(['title' => 'Dependency 1']);
        $dep2 = Task::factory()->create(['title' => 'Dependency 2']);

        TaskDependency::create(['task_id' => $task->id, 'dependency_task_id' => $dep1->id]);
        TaskDependency::create(['task_id' => $task->id, 'dependency_task_id' => $dep2->id]);

        $dependencies = $this->service->getDependencyTasks($task->id);

        $this->assertCount(2, $dependencies);
        $this->assertTrue($dependencies->contains('id', $dep1->id));
        $this->assertTrue($dependencies->contains('id', $dep2->id));
    }

    public function test_are_dependencies_completed_returns_true_when_all_completed()
    {
        $task = Task::factory()->create();
        $dep1 = Task::factory()->completed()->create();
        $dep2 = Task::factory()->completed()->create();

        TaskDependency::create(['task_id' => $task->id, 'dependency_task_id' => $dep1->id]);
        TaskDependency::create(['task_id' => $task->id, 'dependency_task_id' => $dep2->id]);

        $result = $this->service->areDependenciesCompleted($task->id);

        $this->assertTrue($result);
    }

    public function test_are_dependencies_completed_returns_false_when_some_incomplete()
    {
        $task = Task::factory()->create();
        $dep1 = Task::factory()->completed()->create();
        $dep2 = Task::factory()->pending()->create();

        TaskDependency::create(['task_id' => $task->id, 'dependency_task_id' => $dep1->id]);
        TaskDependency::create(['task_id' => $task->id, 'dependency_task_id' => $dep2->id]);

        $result = $this->service->areDependenciesCompleted($task->id);

        $this->assertFalse($result);
    }

    public function test_would_create_circular_dependency_detects_cycles()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();
        $taskC = Task::factory()->create();

        // Create chain: A -> B -> C
        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskA->id]);
        TaskDependency::create(['task_id' => $taskC->id, 'dependency_task_id' => $taskB->id]);

        // Try to create: C -> A (would create cycle)
        $wouldCreateCycle = $this->service->wouldCreateCircularDependency($taskC->id, $taskA->id);

        $this->assertTrue($wouldCreateCycle);
    }

    public function test_would_create_circular_dependency_allows_valid_dependencies()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();
        $taskC = Task::factory()->create();

        // Create: A -> B
        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskA->id]);

        // Try to create: C -> A (should be allowed)
        $wouldCreateCycle = $this->service->wouldCreateCircularDependency($taskC->id, $taskA->id);

        $this->assertFalse($wouldCreateCycle);
    }

    public function test_batch_check_dependencies_completed_handles_multiple_tasks()
    {
        $task1 = Task::factory()->create();
        $task2 = Task::factory()->create();
        $task3 = Task::factory()->create();

        // Task 1: all dependencies completed
        $dep1 = Task::factory()->completed()->create();
        TaskDependency::create(['task_id' => $task1->id, 'dependency_task_id' => $dep1->id]);

        // Task 2: some dependencies incomplete
        $dep2 = Task::factory()->pending()->create();
        TaskDependency::create(['task_id' => $task2->id, 'dependency_task_id' => $dep2->id]);

        // Task 3: no dependencies

        $results = $this->service->batchCheckDependenciesCompleted([
            $task1->id, $task2->id, $task3->id
        ]);

        $this->assertTrue($results[$task1->id]);
        $this->assertFalse($results[$task2->id]);
        $this->assertTrue($results[$task3->id]);
    }

    public function test_caching_works_correctly()
    {
        $task = Task::factory()->create();
        $dependent = Task::factory()->create();
        TaskDependency::create(['task_id' => $dependent->id, 'dependency_task_id' => $task->id]);

        // First call should cache the result
        $result1 = $this->service->getAllDependentTasks($task->id);
        
        // Second call should use cache
        $result2 = $this->service->getAllDependentTasks($task->id);

        $this->assertEquals($result1->toArray(), $result2->toArray());
        $this->assertCount(1, $result1);
    }

    public function test_clear_task_cache_removes_cached_data()
    {
        $task = Task::factory()->create();
        
        // Cache some data
        $this->service->areDependenciesCompleted($task->id);
        
        // Clear cache
        $this->service->clearTaskCache($task->id);
        
        // This should work without issues (no cached data to interfere)
        $result = $this->service->areDependenciesCompleted($task->id);
        $this->assertTrue($result); // No dependencies = completed
    }

    public function test_get_dependency_hierarchy_returns_correct_structure()
    {
        $taskA = Task::factory()->create(['title' => 'Task A']);
        $taskB = Task::factory()->create(['title' => 'Task B']);
        $taskC = Task::factory()->create(['title' => 'Task C']);

        // Create hierarchy: A depends on B, B depends on C
        TaskDependency::create(['task_id' => $taskA->id, 'dependency_task_id' => $taskB->id]);
        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskC->id]);

        $hierarchy = $this->service->getDependencyHierarchy($taskA->id);

        $this->assertCount(3, $hierarchy);
        
        // Check levels are correct
        $taskALevel = collect($hierarchy)->where('id', $taskA->id)->first();
        $taskBLevel = collect($hierarchy)->where('id', $taskB->id)->first();
        $taskCLevel = collect($hierarchy)->where('id', $taskC->id)->first();

        $this->assertEquals(0, $taskALevel->level);
        $this->assertEquals(1, $taskBLevel->level);
        $this->assertEquals(2, $taskCLevel->level);
    }

    public function test_get_dependency_stats_returns_correct_metrics()
    {
        // Create test data
        $task1 = Task::factory()->create();
        $task2 = Task::factory()->create();
        $dep1 = Task::factory()->create();
        $dep2 = Task::factory()->create();

        TaskDependency::create(['task_id' => $task1->id, 'dependency_task_id' => $dep1->id]);
        TaskDependency::create(['task_id' => $task1->id, 'dependency_task_id' => $dep2->id]);
        TaskDependency::create(['task_id' => $task2->id, 'dependency_task_id' => $dep1->id]);

        $stats = $this->service->getDependencyStats();

        $this->assertEquals(2, $stats['tasks_with_dependencies']);
        $this->assertEquals(3, $stats['total_dependencies']);
        $this->assertEquals(1.5, $stats['avg_dependencies_per_task']);
        $this->assertEquals(2, $stats['max_dependencies_per_task']);
    }

    public function test_performance_with_large_dependency_chain()
    {
        // Create a chain of 20 tasks for performance testing
        $tasks = Task::factory()->count(20)->create();
        
        // Create a linear dependency chain
        for ($i = 1; $i < 20; $i++) {
            TaskDependency::create([
                'task_id' => $tasks[$i]->id,
                'dependency_task_id' => $tasks[$i - 1]->id
            ]);
        }

        $startTime = microtime(true);
        
        // This should be fast with CTE optimization
        $dependents = $this->service->getAllDependentTasks($tasks[0]->id);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertCount(19, $dependents);
        $this->assertLessThan(100, $executionTime, 'Query should complete in less than 100ms');
    }
}