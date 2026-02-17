<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|string|in:todo,in_progress,done',
            'deadline' => 'sometimes|date|after:now',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
