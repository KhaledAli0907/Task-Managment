<?php

namespace Tests\Unit;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use Tests\TestCase;

class TaskDependencyModelTest extends TestCase
{
    public function test_task_has_dependencies_relationship()
    {
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask->id
        ]);

        $this->assertCount(1, $task->dependencies);
        $this->assertEquals($dependencyTask->id, $task->dependencies->first()->dependency_task_id);
    }

    public function test_task_has_dependents_relationship()
    {
        $task = Task::factory()->create();
        $dependentTask = Task::factory()->create();

        TaskDependency::create([
            'task_id' => $dependentTask->id,
            'dependency_task_id' => $task->id
        ]);

        $this->assertCount(1, $task->dependents);
        $this->assertEquals($dependentTask->id, $task->dependents->first()->task_id);
    }

    public function test_get_dependency_tasks_returns_task_models()
    {
        $task = Task::factory()->create();
        $dependencyTask1 = Task::factory()->create();
        $dependencyTask2 = Task::factory()->create();

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask1->id
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask2->id
        ]);

        $dependencyTasks = $task->getDependencyTasks();

        $this->assertCount(2, $dependencyTasks);
        $this->assertContains($dependencyTask1->id, $dependencyTasks->pluck('id'));
        $this->assertContains($dependencyTask2->id, $dependencyTasks->pluck('id'));
    }

    public function test_are_dependencies_completed_returns_true_when_no_dependencies()
    {
        $task = Task::factory()->create();

        $this->assertTrue($task->areDependenciesCompleted());
    }

    public function test_are_dependencies_completed_returns_true_when_all_dependencies_completed()
    {
        $task = Task::factory()->create();
        $completedDependency1 = Task::factory()->completed()->create();
        $completedDependency2 = Task::factory()->completed()->create();

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $completedDependency1->id
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $completedDependency2->id
        ]);

        $this->assertTrue($task->areDependenciesCompleted());
    }

    public function test_are_dependencies_completed_returns_false_when_some_dependencies_incomplete()
    {
        $task = Task::factory()->create();
        $completedDependency = Task::factory()->completed()->create();
        $incompleteDependency = Task::factory()->pending()->create();

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $completedDependency->id
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $incompleteDependency->id
        ]);

        $this->assertFalse($task->areDependenciesCompleted());
    }

    public function test_are_dependencies_completed_returns_false_when_all_dependencies_incomplete()
    {
        $task = Task::factory()->create();
        $incompleteDependency1 = Task::factory()->pending()->create();
        $incompleteDependency2 = Task::factory()->inProgress()->create();

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $incompleteDependency1->id
        ]);

        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $incompleteDependency2->id
        ]);

        $this->assertFalse($task->areDependenciesCompleted());
    }

    public function test_get_all_dependent_tasks_returns_recursive_dependents()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();
        $taskC = Task::factory()->create();
        $taskD = Task::factory()->create();

        // taskB depends on taskA
        TaskDependency::create([
            'task_id' => $taskB->id,
            'dependency_task_id' => $taskA->id
        ]);

        // taskC depends on taskB
        TaskDependency::create([
            'task_id' => $taskC->id,
            'dependency_task_id' => $taskB->id
        ]);

        // taskD depends on taskA
        TaskDependency::create([
            'task_id' => $taskD->id,
            'dependency_task_id' => $taskA->id
        ]);

        $dependentTasks = $taskA->getAllDependentTasks();

        $this->assertCount(3, $dependentTasks);
        $this->assertContains($taskB->id, $dependentTasks->pluck('id'));
        $this->assertContains($taskC->id, $dependentTasks->pluck('id'));
        $this->assertContains($taskD->id, $dependentTasks->pluck('id'));
    }

    public function test_get_all_dependent_tasks_returns_empty_when_no_dependents()
    {
        $task = Task::factory()->create();

        $dependentTasks = $task->getAllDependentTasks();

        $this->assertCount(0, $dependentTasks);
    }

    public function test_get_all_dependent_tasks_handles_complex_dependency_chains()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();
        $taskC = Task::factory()->create();
        $taskD = Task::factory()->create();
        $taskE = Task::factory()->create();

        // Create a complex dependency chain:
        // taskB -> taskA
        // taskC -> taskB
        // taskD -> taskA
        // taskE -> taskC

        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskA->id]);
        TaskDependency::create(['task_id' => $taskC->id, 'dependency_task_id' => $taskB->id]);
        TaskDependency::create(['task_id' => $taskD->id, 'dependency_task_id' => $taskA->id]);
        TaskDependency::create(['task_id' => $taskE->id, 'dependency_task_id' => $taskC->id]);

        $dependentTasks = $taskA->getAllDependentTasks();

        $this->assertCount(4, $dependentTasks);
        $this->assertContains($taskB->id, $dependentTasks->pluck('id'));
        $this->assertContains($taskC->id, $dependentTasks->pluck('id'));
        $this->assertContains($taskD->id, $dependentTasks->pluck('id'));
        $this->assertContains($taskE->id, $dependentTasks->pluck('id'));
    }
}
