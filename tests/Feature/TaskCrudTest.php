<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    public function test_manager_can_create_task()
    {
        $manager = $this->createManager();
        $user = $this->createUser();

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => TaskStatus::PENDING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'assignee_id' => $user->id
        ];

        $response = $this->postJson('/api/task', $taskData, $this->withAuth($manager));

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => TaskStatus::PENDING->value,
            'assignee_id' => $user->id
        ]);
    }

    public function test_user_cannot_create_task()
    {
        $user = $this->createUser();

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => TaskStatus::PENDING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'assignee_id' => $user->id
        ];

        $response = $this->postJson('/api/task', $taskData, $this->withAuth($user));

        $response->assertStatus(403);
    }

    public function test_manager_can_update_task()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();

        $updateData = [
            'title' => 'Updated Task Title',
            'description' => 'Updated Description',
            'status' => TaskStatus::IN_PROGRESS->value
        ];

        $response = $this->putJson("/api/task/{$task->id}", $updateData, $this->withAuth($manager));

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'description' => 'Updated Description',
            'status' => TaskStatus::IN_PROGRESS->value
        ]);
    }

    public function test_user_cannot_update_task()
    {
        $user = $this->createUser();
        $task = Task::factory()->create();

        $updateData = [
            'title' => 'Updated Task Title'
        ];

        $response = $this->putJson("/api/task/{$task->id}", $updateData, $this->withAuth($user));

        $response->assertStatus(403);
    }

    public function test_manager_can_delete_task()
    {
        $manager = $this->createManager();
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/task/{$task->id}", [], $this->withAuth($manager));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_task()
    {
        $user = $this->createUser();
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/task/{$task->id}", [], $this->withAuth($user));

        $response->assertStatus(403);
    }

    public function test_authenticated_user_can_view_all_tasks()
    {
        $user = $this->createUser();
        $task1 = Task::factory()->create(['assignee_id' => $user->id]);
        $task2 = Task::factory()->create();

        $response = $this->getJson('/api/task', $this->withAuth($user));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data'); // User should only see assigned tasks
    }

    public function test_manager_can_view_all_tasks()
    {
        $manager = $this->createManager();
        $task1 = Task::factory()->create();
        $task2 = Task::factory()->create();

        $response = $this->getJson('/api/task', $this->withAuth($manager));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_view_specific_task()
    {
        $user = $this->createUser();
        $task = Task::factory()->create(['assignee_id' => $user->id]);

        $response = $this->getJson("/api/task/{$task->id}", $this->withAuth($user));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title
            ]
        ]);
    }

    public function test_task_creation_validation()
    {
        $manager = $this->createManager();

        $response = $this->postJson('/api/task', [], $this->withAuth($manager));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    public function test_task_creation_with_invalid_status()
    {
        $manager = $this->createManager();

        $taskData = [
            'title' => 'Test Task',
            'status' => 'invalid_status',
            'due_date' => now()->addDays(7)->format('Y-m-d')
        ];

        $response = $this->postJson('/api/task', $taskData, $this->withAuth($manager));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_task_creation_with_past_due_date()
    {
        $manager = $this->createManager();

        $taskData = [
            'title' => 'Test Task',
            'status' => TaskStatus::PENDING->value,
            'due_date' => now()->subDays(1)->format('Y-m-d')
        ];

        $response = $this->postJson('/api/task', $taskData, $this->withAuth($manager));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_task_filtering_by_status()
    {
        $user = $this->createUser();
        Task::factory()->pending()->create(['assignee_id' => $user->id]);
        Task::factory()->completed()->create(['assignee_id' => $user->id]);

        $response = $this->getJson('/api/task?status=pending', $this->withAuth($user));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_task_filtering_by_assignee()
    {
        $manager = $this->createManager();
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        Task::factory()->create(['assignee_id' => $user1->id]);
        Task::factory()->create(['assignee_id' => $user2->id]);

        $response = $this->getJson("/api/task?assignee_id={$user1->id}", $this->withAuth($manager));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_task_filtering_by_due_date_range()
    {
        $user = $this->createUser();
        $from = now()->addDays(5)->format('Y-m-d');
        $to = now()->addDays(10)->format('Y-m-d');

        Task::factory()->create([
            'assignee_id' => $user->id,
            'due_date' => now()->addDays(7)
        ]);
        Task::factory()->create([
            'assignee_id' => $user->id,
            'due_date' => now()->addDays(15)
        ]);

        $response = $this->getJson("/api/task?from={$from}&to={$to}", $this->withAuth($user));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }
}
