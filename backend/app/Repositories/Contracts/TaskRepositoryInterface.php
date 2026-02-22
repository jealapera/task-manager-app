<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    /**
     * Get a paginated list of tasks for a user, filtered by date and optional search/sort.
     *
     * @param  array{task_date: string, search?: string, sort_by?: string, sort_dir?: string}  $filters
     */
    public function getForUser(User $user, array $filters): LengthAwarePaginator;

    public function findForUser(User $user, int $id): Task;

    public function create(User $user, array $data): Task;

    public function update(Task $task, array $data): Task;

    public function delete(Task $task): void;

    /**
     * Persist a new sort_order for each task.
     *
     * @param  array<int, array{id: int, sort_order: int}>  $ordered
     */
    public function reorder(User $user, array $ordered): void;
}
