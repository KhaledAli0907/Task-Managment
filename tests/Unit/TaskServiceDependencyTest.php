<?php

namespace Tests\Unit;

use App\Actions\Tasks\TaskFilterAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\Implementations\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TaskServiceDependencyTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $taskService;
    private $mockTaskFilterAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskFilterAction = Mockery::mock(TaskFilterAction::class);
        $this->taskService = new TaskService($this->mockTaskFilterAction);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_add_task_dependency_success()
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        $this->taskService->addTaskDependency($task->id, $dependencyTask->id);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask->id
        ]);
    }

    public function test_add_task_dependency_prevents_self_dependency()
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $task = Task::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Task cannot depend on itself');

        $this->taskService->addTaskDependency($task->id, $task->id);
    }

    public function test_add_task_dependency_prevents_duplicate()
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Add dependency first time
        $this->taskService->addTaskDependency($task->id, $dependencyTask->id);

        // Try to add same dependency again
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dependency already exists');

        $this->taskService->addTaskDependency($task->id, $dependencyTask->id);
    }

    public function test_add_task_dependency_prevents_circular_dependency()
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();

        // Add dependency: taskA depends on taskB
        $this->taskService->addTaskDependency($taskA->id, $taskB->id);

        // Try to add circular dependency: taskB depends on taskA
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This dependency would create a circular reference');

        $this->taskService->addTaskDependency($taskB->id, $taskA->id);
    }

    public function test_remove_task_dependency_success()
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Add dependency first
        $this->taskService->addTaskDependency($task->id, $dependencyTask->id);

        // Remove dependency
        $this->taskService->removeTaskDependency($task->id, $dependencyTask->id);

        $this->assertDatabaseMissing('task_dependencies', [
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask->id
        ]);
    }

    public function test_remove_task_dependency_throws_exception_when_dependency_not_found()
    {
        $manager = $this->createManager();
        $this->actingAs($manager);

        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dependency not found');

        $this->taskService->removeTaskDependency($task->id, $dependencyTask->id);
    }

    public function test_change_task_status_prevents_completion_with_incomplete_dependencies()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);
        $incompleteDependency = Task::factory()->pending()->create();

        // Add incomplete dependency
        $manager = $this->createManager();
        $this->actingAs($manager);
        $this->taskService->addTaskDependency($task->id, $incompleteDependency->id);

        // Switch back to user
        $this->actingAs($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot complete task: dependencies not completed');

        $this->taskService->changeTaskStatus($task->id, TaskStatus::COMPLETED->value);
    }

    public function test_change_task_status_allows_completion_when_dependencies_completed()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);
        $completedDependency = Task::factory()->completed()->create();

        // Add completed dependency
        $manager = $this->createManager();
        $this->actingAs($manager);
        $this->taskService->addTaskDependency($task->id, $completedDependency->id);

        // Switch back to user
        $this->actingAs($user);

        // Should not throw exception
        $this->taskService->changeTaskStatus($task->id, TaskStatus::COMPLETED->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::COMPLETED->value
        ]);
    }

    public function test_change_task_status_prevents_user_from_updating_unassigned_task()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $this->actingAs($user);

        $task = Task::factory()->create(['assignee_id' => $otherUser->id]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You can only update tasks assigned to you');

        $this->taskService->changeTaskStatus($task->id, TaskStatus::COMPLETED->value);
    }

    public function test_change_task_status_allows_user_to_update_assigned_task()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);

        // Should not throw exception
        $this->taskService->changeTaskStatus($task->id, TaskStatus::IN_PROGRESS->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::IN_PROGRESS->value
        ]);
    }

    public function test_change_task_status_allows_manager_to_update_any_task()
    {
        $manager = $this->createManager();
        $user = $this->createUser();
        $this->actingAs($manager);

        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);

        // Should not throw exception
        $this->taskService->changeTaskStatus($task->id, TaskStatus::COMPLETED->value);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::COMPLETED->value
        ]);
    }
}
