<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'matt@goteam.ph')->firstOrFail();

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $twoDaysAgo = now()->subDays(2)->toDateString();
        $lastWeek = now()->subDays(7)->toDateString();

        $tasks = [
            // Today — mix of pending and completed
            ['statement' => 'Review pull requests from the team', 'status' => 'pending',   'priority' => 2, 'task_date' => $today,      'sort_order' => 1],
            ['statement' => 'Update project documentation',       'status' => 'pending',   'priority' => 1, 'task_date' => $today,      'sort_order' => 2],
            ['statement' => 'Fix login bug reported by QA',       'status' => 'completed', 'priority' => 3, 'task_date' => $today,      'sort_order' => 3],
            ['statement' => 'Deploy hotfix to staging',           'status' => 'pending',   'priority' => 3, 'task_date' => $today,      'sort_order' => 4],

            // Yesterday
            ['statement' => 'Set up CI/CD pipeline',              'status' => 'completed', 'priority' => 2, 'task_date' => $yesterday,  'sort_order' => 1],
            ['statement' => 'Write unit tests for task module',   'status' => 'completed', 'priority' => 2, 'task_date' => $yesterday,  'sort_order' => 2],
            ['statement' => 'Sync with design team on new mockups', 'status' => 'pending', 'priority' => 1, 'task_date' => $yesterday,  'sort_order' => 3],

            // Two days ago
            ['statement' => 'Migrate database to new schema',     'status' => 'completed', 'priority' => 3, 'task_date' => $twoDaysAgo, 'sort_order' => 1],
            ['statement' => 'Code review for onboarding feature', 'status' => 'completed', 'priority' => 1, 'task_date' => $twoDaysAgo, 'sort_order' => 2],

            // Last week
            ['statement' => 'Sprint planning for next cycle',     'status' => 'completed', 'priority' => 2, 'task_date' => $lastWeek,   'sort_order' => 1],
            ['statement' => 'Research new caching strategies',    'status' => 'completed', 'priority' => 0, 'task_date' => $lastWeek,   'sort_order' => 2],
        ];

        foreach ($tasks as $task) {
            Task::create(array_merge($task, ['user_id' => $user->id]));
        }
    }
}
