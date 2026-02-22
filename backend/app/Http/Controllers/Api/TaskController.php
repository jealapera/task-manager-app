<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ReorderTasksRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(private readonly TaskRepositoryInterface $tasks) {}

    /**
     * Display a paginated listing of tasks for a given date.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        $request->validate([
            'task_date' => ['required_without:search', 'nullable', 'date_format:Y-m-d'],
            'search' => ['sometimes', 'string', 'max:255'],
            'sort_by' => ['sometimes', 'in:sort_order,priority,status,created_at'],
            'sort_dir' => ['sometimes', 'in:asc,desc'],
        ]);

        $tasks = $this->tasks->getForUser($request->user(), $request->only([
            'task_date', 'search', 'sort_by', 'sort_dir',
        ]));

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): TaskResource
    {
        $this->authorize('create', Task::class);

        $task = $this->tasks->create($request->user(), $request->validated());

        return new TaskResource($task);
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        $task = $this->tasks->update($task, $request->validated());

        return new TaskResource($task);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->tasks->delete($task);

        return response()->json(null, 204);
    }

    /**
     * Persist drag-and-drop sort order for tasks.
     */
    public function reorder(ReorderTasksRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);

        $this->tasks->reorder($request->user(), $request->validated('tasks'));

        return response()->json(['message' => 'Order saved.']);
    }
}
