<?php

namespace App\Services\Implementations;

use App\Actions\Tasks\TaskFilterAction;
use App\Models\Task;
use App\Services\Interfaces\TaskServiceInterface;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
}
