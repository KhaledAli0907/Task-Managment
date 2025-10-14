<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Requests\TaskDependencyRequest;
use App\Models\Task;
use App\Services\Interfaces\TaskServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ResponseTrait;
    public function __construct(private TaskServiceInterface $taskService)
    {
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to create tasks');
        }
        $data = $request->validated();

        // Extract children data if present
        $childrenData = $data['children'] ?? [];
        unset($data['children']);

        if (!empty($childrenData)) {
            $task = $this->taskService->createTaskWithChildren($data, $childrenData);
            return $this->success201($task, 'Task with children created successfully');
        } else {
            $task = $this->taskService->createTask($data);
            return $this->success201($task, 'Task created successfully');
        }
    }

    public function index()
    {
        $result = $this->taskService->getTasks();
        return $this->success200($result, 'Tasks fetched successfully');
    }

    public function show(string $id)
    {
        $result = $this->taskService->getTask($id);
        return $this->success200($result, 'Task fetched successfully');
    }

    public function update(string $id, TaskUpdateRequest $request)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to update this task');
        }
        $data = $request->validated();
        $task = $this->taskService->updateTask($id, $data);
        return $this->success200($task, 'Task updated successfully');
    }

    public function destroy(string $id)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to delete tasks');
        }
        return $this->taskService->deleteTask($id);
    }

    public function assign(string $id, Request $request)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to assign tasks');
        }
        try {
            $assigneeId = $request->input('assignee_id');
            $this->taskService->assignTask($id, $assigneeId);
            return $this->success200(null, 'Task assigned successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }

    public function changeTaskStatus(string $id, Request $request)
    {
        try {
            $status = $request->input('status');
            $this->taskService->changeTaskStatus($id, $status);
            return $this->success200(null, 'Task status changed successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }

    public function addChild(string $id, TaskStoreRequest $request)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to manage child tasks');
        }

        try {
            $childData = $request->validated();
            $childTask = $this->taskService->addChildTask($id, $childData);
            return $this->success201($childTask, 'Child task added successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }

    public function removeChild(string $childId)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to manage child tasks');
        }

        try {
            $this->taskService->removeChildTask($childId);
            return $this->success200(null, 'Child task removed successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }
}
