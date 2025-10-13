<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskStatusUpdateTest extends TestCase
{
    public function test_user_can_update_status_of_assigned_task()
    {
        $user = $this->createUser();
        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);

        $response = $this->patchJson("/api/task/{$task->id}/status", [
            'status' => TaskStatus::IN_PROGRESS->value
        ], $this->withAuth($user));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task status changed successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatus::IN_PROGRESS->value
        ]);
    }

    public function test_user_cannot_update_status_of_unassigned_task()
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

    public function test_manager_cannot_update_task_status_directly()
    {
        $manager = $this->createManager();
        $user = $this->createUser();
        $task = Task::factory()->create(['assignee_id' => $user->id]);

        $response = $this->patchJson("/api/task/{$task->id}/status", [
            'status' => TaskStatus::COMPLETED->value
        ], $this->withAuth($manager));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'errors' => 'You are not authorized to change task status'
            ]);
    }

    public function test_cannot_update_status_of_nonexistent_task()
    {
        $user = $this->createUser();

        $response = $this->patchJson("/api/task/nonexistent-id/status", [
            'status' => TaskStatus::COMPLETED->value
        ], $this->withAuth($user));

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'errors' => 'Task not found'
            ]);
    }

    public function test_requires_authentication_for_status_update()
    {
        $task = Task::factory()->create();

        $response = $this->patchJson("/api/task/{$task->id}/status", [
            'status' => TaskStatus::COMPLETED->value
        ]);

        $response->assertStatus(401);
    }

    public function test_can_update_to_all_valid_statuses()
    {
        $user = $this->createUser();
        $task = Task::factory()->pending()->create(['assignee_id' => $user->id]);

        $statuses = [
            TaskStatus::IN_PROGRESS->value,
            TaskStatus::COMPLETED->value,
            TaskStatus::ARCHIVED->value
        ];

        foreach ($statuses as $status) {
            $response = $this->patchJson("/api/task/{$task->id}/status", [
                'status' => $status
            ], $this->withAuth($user));

            $response->assertStatus(200);

            $this->assertDatabaseHas('tasks', [
                'id' => $task->id,
                'status' => $status
            ]);
        }
    }
}
