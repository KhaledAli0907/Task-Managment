<?php

namespace App\Services\Implementations;

use App\Actions\Tasks\TaskFilterAction;
use App\Actions\Tasks\TaskStatusValidationAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\Interfaces\TaskServiceInterface;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Log;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        protected TaskFilterAction $taskFilterAction,
    ) {
    }
    public function createTask(array $data): Task
    {
        DB::beginTransaction();
        try {
            $task = Task::create($data);
            Log::info('Task created successfully', ['task' => $task, 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            DB::commit();
            return $task;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create task', ['error' => $e->getMessage(), 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            throw new \Exception('Failed to create task');
        }
    }


    public function assignTask(string $id, int $assigneeId): void
    {
        $task = Task::find($id);
        if (!$task) {
            throw new \InvalidArgumentException('Task not found');
        }
        if (!auth()->user()->isManager()) {
            throw new \Exception('You are not authorized to assign tasks');
        }
        if (!User::find($assigneeId)) {
            throw new \InvalidArgumentException('Assignee not found');
        }
        DB::beginTransaction();
        try {
            $task->assignee_id = $assigneeId;
            $task->save();
            Log::info('Task assigned successfully', ['task' => $task, 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign task', ['error' => $e->getMessage(), 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            throw new \Exception('Failed to assign task');
        }
    }

    public function changeTaskStatus(string $id, string $status): void
    {
        $task = Task::find($id);

        // Validate task status update
        app(TaskStatusValidationAction::class)->handle($task, $status);

        DB::beginTransaction();
        try {
            $task->status = $status;
            $task->save();
            Log::info('Task status changed successfully', ['task' => $task, 'manager' => auth()->user()->email, 'ip' => request()->ip()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to change task status', ['error' => $e->getMessage(), 'manager' => auth()->user()->email, 'ip' => request()->ip()]);
            throw new \Exception('Failed to change task status');
        }
    }

    public function getTasks(): Collection
    {
        $query = Task::query()->with([
            'assignee:id,name,email',
            'parent:id,title',
            'children:id,title,status'
        ]);

        $tasks = $this->taskFilterAction->handle($query);

        return $tasks->get();
    }

    public function getTask(string $id): Task
    {
        return Task::findOrFail($id)->with([
            'assignee:id,name,email',
            'parent:id,title',
            'children:id,title,status'
        ])->first();
    }

    public function updateTask(string $id, array $data): Task
    {
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            $task->update($data);
            Log::info('Task updated successfully', ['task' => $task, 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update task', ['error' => $e->getMessage(), 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            throw new \Exception('Failed to update task');
        }
        return $task;
    }

    public function deleteTask(string $id): void
    {
        DB::beginTransaction();
        try {
            $task = Task::findOrFail($id);
            $task->delete();
            Log::info('Task deleted successfully', ['task' => $task, 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete task', ['error' => $e->getMessage(), 'manager' => auth()->user()->name, 'ip' => request()->ip()]);
            throw new \Exception('Failed to delete task');
        }
    }

    public function createTaskWithChildren(array $parentData, array $childrenData = []): Task
    {
        DB::beginTransaction();
        try {
            // Create parent task
            $parentTask = Task::create($parentData);

            // Create child tasks if provided
            if (!empty($childrenData)) {
                foreach ($childrenData as $childData) {
                    $childData['parent_task_id'] = $parentTask->id;
                    $childTask = Task::create($childData);
                    $childTask->validateParentChildRelationship();
                }
            }

            Log::info('Task with children created successfully', [
                'parent_task_id' => $parentTask->id,
                'children_count' => count($childrenData),
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);

            DB::commit();
            return $parentTask->load('children');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create task with children', [
                'error' => $e->getMessage(),
                'parent_data' => $parentData,
                'children_data' => $childrenData,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Failed to create task with children');
        }
    }

    public function addChildTask(string $parentTaskId, array $childData): Task
    {
        $parentTask = Task::findOrFail($parentTaskId);

        // Validate that parent is not already a child
        if ($parentTask->isChild()) {
            throw new \InvalidArgumentException('Cannot add children to a child task');
        }

        DB::beginTransaction();
        try {
            $childData['parent_task_id'] = $parentTaskId;
            $childTask = Task::create($childData);
            $childTask->validateParentChildRelationship();

            Log::info('Child task added successfully', [
                'parent_task_id' => $parentTaskId,
                'child_task_id' => $childTask->id,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);

            DB::commit();
            return $childTask;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add child task', [
                'error' => $e->getMessage(),
                'parent_task_id' => $parentTaskId,
                'child_data' => $childData,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Failed to add child task');
        }
    }

    public function removeChildTask(string $childTaskId): void
    {
        $childTask = Task::findOrFail($childTaskId);

        if (!$childTask->isChild()) {
            throw new \InvalidArgumentException('Task is not a child task');
        }

        DB::beginTransaction();
        try {
            $childTask->delete();

            Log::info('Child task removed successfully', [
                'child_task_id' => $childTaskId,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove child task', [
                'error' => $e->getMessage(),
                'child_task_id' => $childTaskId,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Failed to remove child task');
        }
    }
}
