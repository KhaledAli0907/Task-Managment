<?php

namespace App\Services\Implementations;

use App\Actions\Tasks\TaskFilterAction;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use App\Services\Interfaces\TaskServiceInterface;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\Actions\Tasks\ValidateDependencyBeforeAdding;
use Log;

class TaskService implements TaskServiceInterface
{
    public function __construct(protected TaskFilterAction $taskFilterAction)
    {
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
        if (!$task) {
            throw new \InvalidArgumentException('Task not found');
        }

        // Check if user can update this task
        if (auth()->user()->isUser() && $task->assignee_id !== auth()->id()) {
            throw new \Exception('You can only update tasks assigned to you');
        }

        // Prevent completion if dependencies not met
        if ($status === TaskStatus::COMPLETED->value && !$task->areDependenciesCompleted()) {
            throw new \Exception('Cannot complete task: dependencies not completed');
        }

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
            'dependencies:id,title,status'
        ]);

        $tasks = $this->taskFilterAction->handle($query);

        return $tasks->get();
    }

    public function getTask(string $id): Task
    {
        return Task::findOrFail($id)->with([
            'assignee',
            'dependencies'
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

    public function addTaskDependency(string $taskId, string $dependencyTaskId): void
    {
        $task = app(ValidateDependencyBeforeAdding::class)->handle($taskId, $dependencyTaskId);

        DB::beginTransaction();
        try {
            $task->dependencies()->create([
                'dependency_task_id' => $dependencyTaskId
            ]);
            Log::info('Task dependency added successfully', [
                'task_id' => $taskId,
                'dependency_task_id' => $dependencyTaskId,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add task dependency', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'dependency_task_id' => $dependencyTaskId,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Failed to add task dependency');
        }
    }

    public function removeTaskDependency(string $taskId, string $dependencyTaskId): void
    {
        $task = Task::findOrFail($taskId);

        $dependency = $task->dependencies()->where('dependency_task_id', $dependencyTaskId)->first();
        if (!$dependency) {
            throw new \InvalidArgumentException('Dependency not found');
        }

        DB::beginTransaction();
        try {
            $dependency->delete();
            Log::info('Task dependency removed successfully', [
                'task_id' => $taskId,
                'dependency_task_id' => $dependencyTaskId,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove task dependency', [
                'error' => $e->getMessage(),
                'task_id' => $taskId,
                'dependency_task_id' => $dependencyTaskId,
                'manager' => auth()->user()->email,
                'ip' => request()->ip()
            ]);
            throw new \Exception('Failed to remove task dependency');
        }
    }
}
