<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private TaskServiceInterface $taskService)
    {
    }

    public function store(TaskStoreRequest $request)
    {
        $data = $request->validated();
        $data['assignee_id'] = auth()->user()->id;
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
        return $this->taskService->updateTask($id, $request->validated());
    }

    public function destroy(string $id)
    {
        return $this->taskService->deleteTask($id);
    }
}
