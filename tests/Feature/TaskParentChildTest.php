<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskParentChildTest extends TestCase
{
    public function test_manager_can_create_task_with_children()
    {
        $manager = $this->createManager();
        $user = $this->createUser();

        $taskData = [
            'title' => 'Parent Task',
            'description' => 'Parent task description',
            'status' => TaskStatus::PENDING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'assignee_id' => $user->id,
            'children' => [
                [
                    'title' => 'Child Task 1',
                    'description' => 'Child task 1 description',
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => now()->addDays(5)->format('Y-m-d'),
                    'assignee_id' => $user->id
                ],
                [
                    'title' => 'Child Task 2',
                    'description' => 'Child task 2 description',
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => now()->addDays(6)->format('Y-m-d'),
                    'assignee_id' => $user->id
                ]
            ]
        ];

        $response = $this->postJson('/api/task', $taskData, $this->withAuth($manager));

        $response->assertStatus(201);
        $responseData = $response->json();

        // Check parent task was created
        $this->assertArrayHasKey('data', $responseData);
        $parentTask = $responseData['data'];
        $this->assertEquals('Parent Task', $parentTask['title']);

        // Check children were created
        $this->assertArrayHasKey('children', $parentTask);
        $this->assertCount(2, $parentTask['children']);

        // Verify in database
        $this->assertDatabaseHas('tasks', [
            'title' => 'Parent Task',
            'parent_task_id' => null
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Child Task 1',
            'parent_task_id' => $parentTask['id']
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Child Task 2',
            'parent_task_id' => $parentTask['id']
        ]);
    }

    public function test_user_cannot_create_task_with_children()
    {
        $user = $this->createUser();

        $taskData = [
            'title' => 'Parent Task',
            'status' => TaskStatus::PENDING->value,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'children' => [
                [
                    'title' => 'Child Task 1',
                    'status' => TaskStatus::PENDING->value,
                    'due_date' => now()->addDays(5)->format('Y-m-d')
                ]
            ]
        ];

        $response = $this->postJson('/api/task', $taskData, $this->withAuth($user));

        $response->assertStatus(403);
    }
}
