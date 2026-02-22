<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'statement' => ['sometimes', 'string', 'max:500'],
            'status' => ['sometimes', 'in:pending,completed'],
            'priority' => ['sometimes', 'integer', 'between:0,3'],
            'task_date' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }
}
