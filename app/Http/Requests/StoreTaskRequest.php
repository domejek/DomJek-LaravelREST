<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|string|in:todo,in_progress,done',
            'deadline' => 'required|date|after:now',
            'project_id' => 'nullable|exists:projects,id',
        ];
    }
}
