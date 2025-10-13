<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDependencyTest extends TestCase
{
    use RefreshDatabase;
    public function test_manager_can_add_task_dependency()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'message' => 'Task dependency added successfully'
            ]);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask->id
        ]);
    }

    public function test_user_cannot_add_task_dependency()
    {
        $user = $this->createUser();
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($user));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'errors' => 'You are not authorized to manage task dependencies'
            ]);
    }

    public function test_cannot_add_self_dependency()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $task->id
        ], $this->withAuth($manager));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dependency_task_id']);
    }

    public function test_cannot_add_duplicate_dependency()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Add dependency first time
        $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        // Try to add same dependency again
        $response = $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'Dependency already exists'
            ]);
    }

    public function test_cannot_add_circular_dependency()
    {
        $manager = $this->createManager();
        $taskA = Task::factory()->create();
        $taskB = Task::factory()->create();

        // Add dependency: taskA depends on taskB
        $this->postJson("/api/task/{$taskA->id}/dependencies", [
            'dependency_task_id' => $taskB->id
        ], $this->withAuth($manager));

        // Try to add circular dependency: taskB depends on taskA
        $response = $this->postJson("/api/task/{$taskB->id}/dependencies", [
            'dependency_task_id' => $taskA->id
        ], $this->withAuth($manager));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'This dependency would create a circular reference'
            ]);
    }

    public function test_manager_can_remove_task_dependency()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Add dependency first
        $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        // Remove dependency
        $response = $this->deleteJson("/api/task/{$task->id}/dependencies/{$dependencyTask->id}", [], $this->withAuth($manager));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task dependency removed successfully'
            ]);

        $this->assertDatabaseMissing('task_dependencies', [
            'task_id' => $task->id,
            'dependency_task_id' => $dependencyTask->id
        ]);
    }

    public function test_user_cannot_remove_task_dependency()
    {
        $manager = $this->createManager();
        $user = $this->createUser();


        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Manager adds dependency
        $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        // User tries to remove dependency
        // Clear any existing authentication
        auth()->logout();
        $response = $this->deleteJson("/api/task/{$task->id}/dependencies/{$dependencyTask->id}", [], $this->withAuth($user));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'errors' => 'You are not authorized to manage task dependencies'
            ]);
    }

    public function test_cannot_remove_nonexistent_dependency()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        $response = $this->deleteJson("/api/task/{$task->id}/dependencies/{$dependencyTask->id}", [], $this->withAuth($manager));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'Dependency not found'
            ]);
    }

    public function test_cannot_complete_task_with_incomplete_dependencies()
    {
        $user = $this->createUser();
        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);
        $dependencyTask = Task::factory()->pending()->create();

        // Add dependency
        $manager = $this->createManager();
        $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        // Try to complete task with incomplete dependency
        auth()->logout();
        $response = $this->patchJson("/api/task/{$task->id}/status", [
            'status' => TaskStatus::COMPLETED->value
        ], $this->withAuth($user));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'Cannot complete task: dependencies not completed'
            ]);
    }

    public function test_can_complete_task_when_all_dependencies_completed()
    {
        $user = $this->createUser();
        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);
        $dependencyTask = Task::factory()->completed()->create();

        // Add dependency
        $manager = $this->createManager();
        $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ], $this->withAuth($manager));

        // Complete task - should succeed
        auth()->logout();
        $response = $this->patchJson("/api/task/{$task->id}/status", [
            'status' => TaskStatus::COMPLETED->value
        ], $this->withAuth($user));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task status changed successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::COMPLETED->value
        ]);
    }

    public function test_user_can_only_update_assigned_tasks()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $task = Task::factory()->create(['assignee_id' => $otherUser->id]);

        $response = $this->patchJson("/api/task/{$task->id}/status", [
            'status' => TaskStatus::COMPLETED->value
        ], $this->withAuth($user));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'You can only update tasks assigned to you'
            ]);
    }

    public function test_validation_errors_for_invalid_dependency_task_id()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => 'invalid-uuid'
        ], $this->withAuth($manager));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dependency_task_id']);
    }

    public function test_validation_errors_for_missing_dependency_task_id()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/dependencies", [], $this->withAuth($manager));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['dependency_task_id']);
    }

    public function test_requires_authentication_for_dependency_operations()
    {
        $task = Task::factory()->create();
        $dependencyTask = Task::factory()->create();

        // Test add dependency without auth
        $response = $this->postJson("/api/task/{$task->id}/dependencies", [
            'dependency_task_id' => $dependencyTask->id
        ]);

        $response->assertStatus(401);

        // Test remove dependency without auth
        $response = $this->deleteJson("/api/task/{$task->id}/dependencies/{$dependencyTask->id}");

        $response->assertStatus(401);
    }
}
