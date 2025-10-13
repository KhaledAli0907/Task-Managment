<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    public function test_manager_can_assign_task_to_user()
    {
        $manager = $this->createManager();
        $user = $this->createUser();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/assign", [
            'assignee_id' => $user->id
        ], $this->withAuth($manager));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task assigned successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assignee_id' => $user->id
        ]);
    }

    public function test_user_cannot_assign_tasks()
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/assign", [
            'assignee_id' => $otherUser->id
        ], $this->withAuth($user));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'errors' => 'You are not authorized to assign tasks'
            ]);
    }

    public function test_cannot_assign_task_to_nonexistent_user()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/assign", [
            'assignee_id' => 99999
        ], $this->withAuth($manager));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'Assignee not found'
            ]);
    }

    public function test_cannot_assign_nonexistent_task()
    {
        $manager = $this->createManager();
        $user = $this->createUser();

        $response = $this->postJson("/api/task/nonexistent-id/assign", [
            'assignee_id' => $user->id
        ], $this->withAuth($manager));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'Task not found'
            ]);
    }

    public function test_requires_authentication_for_task_assignment()
    {
        $user = $this->createUser();
        $task = Task::factory()->create();

        $response = $this->postJson("/api/task/{$task->id}/assign", [
            'assignee_id' => $user->id
        ]);

        $response->assertStatus(401);
    }
}
