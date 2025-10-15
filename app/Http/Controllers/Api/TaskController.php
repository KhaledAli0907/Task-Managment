<?php

namespace App\Http\Controllers\Api;

use App\Enums\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\TaskStoreRequest;
use App\Http\Requests\Tasks\TaskUpdateRequest;
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
        // Lock manager-only functions using constructor middleware
        $this->middleware(['permission:' . PermissionEnum::TASK_CREATE->value])->only(['store']);
        $this->middleware(['permission:' . PermissionEnum::TASK_READ->value])->only(['index', 'show']);
        $this->middleware(['permission:' . PermissionEnum::TASK_DELETE->value])->only(['destroy']);
        $this->middleware(['permission:' . PermissionEnum::TASK_ASSIGN->value])->only(['assign']);
        $this->middleware(['permission:' . PermissionEnum::TASK_MANAGE_CHILDREN->value])->only(['addChild', 'removeChild']);
    }

    public function store(TaskStoreRequest $request): JsonResponse
    {
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
        // TaskFilterAction already handles user role filtering
        $result = $this->taskService->getTasks();
        return $this->success200($result, 'Tasks fetched successfully');
    }

    public function show(string $id)
    {
        // TaskFilterAction already handles user role filtering
        $result = $this->taskService->getTask($id);
        return $this->success200($result, 'Task fetched successfully');
    }

    public function update(string $id, TaskUpdateRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();

        // Check if user can update tasks
        if ($user->can(PermissionEnum::TASK_UPDATE->value)) {
            $task = $this->taskService->updateTask($id, $data);
            return $this->success200($task, 'Task updated successfully');
        }

        // Check if user can only update status
        if ($user->can(PermissionEnum::TASK_STATUS_UPDATE->value)) {
            try {
                // Check if task is assigned to this user using filtered query
                $task = $this->taskService->getTask($id);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return $this->error403('You are not authorized to update this task');
            }

            // Only allow status updates
            if (isset($data['status'])) {
                $task = $this->taskService->updateTaskStatus($id, $data['status']);
                return $this->success200($task, 'Task status updated successfully');
            } else {
                return $this->error403('You can only update the status of tasks assigned to you');
            }
        }

        return $this->error403('You are not authorized to update this task');
    }

    public function destroy(string $id)
    {
        return $this->taskService->deleteTask($id);
    }

    public function assign(string $id, Request $request)
    {
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
            $user = auth()->user();
            $status = $request->input('status');

            // Check if user can change status of any task
            if ($user->can(PermissionEnum::TASK_UPDATE->value)) {
                $this->taskService->changeTaskStatus($id, $status);
                return $this->success200(null, 'Task status changed successfully');
            }

            // Check if user can only change status of assigned tasks
            if ($user->can(PermissionEnum::TASK_STATUS_UPDATE->value)) {
                try {
                    // Check if task is assigned to this user using filtered query
                    $task = $this->taskService->getTask($id);
                } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                    return $this->error403('You are not authorized to change the status of this task');
                }

                $this->taskService->changeTaskStatus($id, $status);
                return $this->success200(null, 'Task status changed successfully');
            }

            return $this->error403('You are not authorized to change task status');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }

    public function addChild(string $id, TaskStoreRequest $request)
    {
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
        try {
            $this->taskService->removeChildTask($childId);
            return $this->success200(null, 'Child task removed successfully');
        } catch (\Exception $e) {
            return $this->error500($e->getMessage());
        }
    }
}
