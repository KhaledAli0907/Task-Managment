<?php

namespace App\Services\Interfaces;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface TaskServiceInterface
{
    /**
     * Creates a new task with the provided data
     *
     * @param array $data Array containing task data with the following keys:
     *                    - title (string): The title of the task
     *                    - description (string): Detailed description of the task
     *                    - due_date (string): Due date in Y-m-d format
     *                    - assigned_to (int): User ID of assignee
     *                    - status (string): Current status of the task
     * @return Task The newly created task instance
     */
    public function createTask(array $data): Task;

    public function getTasks(): Collection;

    public function getTask(string $id): Task;

    public function updateTask(string $id, array $data): Task;

    public function deleteTask(string $id): void;

    public function assignTask(string $id, int $assigneeId): void;

    public function changeTaskStatus(string $id, string $status): void;

    public function createTaskWithChildren(array $parentData, array $childrenData = []): Task;

    public function addChildTask(string $parentTaskId, array $childData): Task;

    public function removeChildTask(string $childTaskId): void;
}
