<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
        'priority' => 0,
        'sort_order' => 0,
    ];

    protected $fillable = [
        'user_id',
        'statement',
        'status',
        'priority',
        'task_date',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'task_date' => 'date',
            'priority' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
