<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Http\Requests\TaskDependencyRequest;
use App\Services\Interfaces\TaskServiceInterface;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ResponseTrait;
    public function __construct(private TaskServiceInterface $taskService)
    {
    }

    public function store(TaskStoreRequest $request)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to create tasks');
        }
        $data = $request->validated();
        return $this->taskService->createTask($data);
    }

    public function index()
    {
        return $this->taskService->getTasks();
    }

    public function show(string $id)
    {
        return $this->taskService->getTask($id);
    }

    public function update(string $id, TaskUpdateRequest $request)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to update tasks');
        }
        return $this->taskService->updateTask($id, $request->validated());
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
        // Only users can change task status
        // Users can only change status of tasks assigned to them
        if (!auth()->user()->isUser()) {
            return $this->error403('You are not authorized to change task status');
        }
        try {
            $status = $request->input('status');
            $this->taskService->changeTaskStatus($id, $status);
            return $this->success200(null, 'Task status changed successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }

    public function addDependency(string $id, TaskDependencyRequest $request)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to manage task dependencies');
        }

        try {
            $this->taskService->addTaskDependency($id, $request->dependency_task_id);
            return $this->success200(null, 'Task dependency added successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }

    public function removeDependency(string $id, string $dependencyId)
    {
        if (!auth()->user()->isManager()) {
            return $this->error403('You are not authorized to manage task dependencies');
        }

        try {
            $this->taskService->removeTaskDependency($id, $dependencyId);
            return $this->success200(null, 'Task dependency removed successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }
}
