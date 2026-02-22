<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskRepository implements TaskRepositoryInterface
{
    public function getForUser(User $user, array $filters): LengthAwarePaginator
    {
        $query = $user->tasks();

        // Scope to a date only when not doing a global search
        if (empty($filters['search']) && ! empty($filters['task_date'])) {
            $query->whereDate('task_date', $filters['task_date']);
        }

        if (! empty($filters['search'])) {
            $query->where('statement', 'like', '%'.$filters['search'].'%');
        }

        $sortBy = $filters['sort_by'] ?? 'sort_order';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $allowedSorts = ['sort_order', 'priority', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }

        $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');

        return $query->paginate(50);
    }

    public function findForUser(User $user, int $id): Task
    {
        $task = $user->tasks()->find($id);

        if (! $task) {
            throw new ModelNotFoundException("Task [{$id}] not found for this user.");
        }

        return $task;
    }

    public function create(User $user, array $data): Task
    {
        // Place new task at end of list for that date
        $maxOrder = $user->tasks()
            ->whereDate('task_date', $data['task_date'])
            ->max('sort_order') ?? -1;

        $data['sort_order'] = $maxOrder + 1;

        return $user->tasks()->create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function reorder(User $user, array $ordered): void
    {
        foreach ($ordered as $item) {
            $user->tasks()
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
    }
}
