<?php

namespace Tests\Unit;

use App\Actions\Tasks\ValidateDependencyBeforeAdding;
use App\Models\Task;
use App\Models\TaskDependency;
use Tests\TestCase;

class ValidateDependencyBeforeAddingTest extends TestCase
{
    private ValidateDependencyBeforeAdding $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ValidateDependencyBeforeAdding();
    }

    public function test_handle_returns_task_when_validation_passes()
    {
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        $result = $this->action->handle($task->id, $dependencyTask->id);

        $this->assertInstanceOf(Task::class, $result);
        $this->assertEquals($task->id, $result->id);
    }

    public function test_handle_throws_exception_for_self_dependency()
    {
        $task = Task::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task cannot depend on itself');

        $this->action->handle($task->id, $task->id);
    }

    public function test_handle_throws_exception_for_existing_dependency()
    {
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Create existing dependency
        TaskDependency::create([
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask->id
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dependency already exists');

        $this->action->handle($task->id, $dependencyTask->id);
    }

    public function test_handle_throws_exception_for_circular_dependency()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();

        // Create dependency: taskA depends on taskB
        TaskDependency::create([
            'task_id' => $taskA->id,
            'dependency_task_id' => $taskB->id
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This dependency would create a circular reference');

        // Try to create circular dependency: taskB depends on taskA
        $this->action->handle($taskB->id, $taskA->id);
    }

    public function test_handle_throws_exception_for_complex_circular_dependency()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();
        $taskC = Task::factory()->create();

        // Create dependency chain: taskA -> taskB -> taskC
        TaskDependency::create(['task_id' => $taskA->id, 'dependency_task_id' => $taskB->id]);
        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskC->id]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This dependency would create a circular reference');

        // Try to create circular dependency: taskC depends on taskA
        $this->action->handle($taskC->id, $taskA->id);
    }

    public function test_handle_allows_valid_dependency_chain()
    {
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();
        $taskC = Task::factory()->create();

        // Create valid dependency chain: taskA -> taskB -> taskC
        TaskDependency::create(['task_id' => $taskA->id, 'dependency_task_id' => $taskB->id]);
        TaskDependency::create(['task_id' => $taskB->id, 'dependency_task_id' => $taskC->id]);

        // This should not throw an exception - adding a new task that depends on taskC
        $taskD = Task::factory()->create();
        $result = $this->action->handle($taskD->id, $taskC->id);
        $this->assertInstanceOf(Task::class, $result);
    }

    public function test_handle_throws_exception_for_nonexistent_task()
    {
        $task = Task::factory()->create();
        $nonexistentTaskId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->handle($task->id, $nonexistentTaskId);
    }

    public function test_handle_throws_exception_for_nonexistent_dependency_task()
    {
        $task = Task::factory()->create();
        $nonexistentTaskId = '00000000-0000-0000-0000-000000000000';

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->handle($nonexistentTaskId, $task->id);
    }
}
