<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\TaskStatus;

class TaskStoreRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', 'string', Rule::in(TaskStatus::cases())],
            'completed' => 'boolean',
            'due_date' => 'nullable|date|after:now',
            'assignee_id' => 'nullable|exists:users,id',
            'children' => 'nullable|array',
            'children.*.title' => 'required|string|max:255',
            'children.*.description' => 'nullable|string',
            'children.*.status' => ['required', 'string', Rule::in(TaskStatus::cases())],
            'children.*.due_date' => 'nullable|date|after:now',
            'children.*.assignee_id' => 'nullable|exists:users,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The task title is required.',
            'title.max' => 'The task title may not be greater than 255 characters.',
            'status.required' => 'The task status is required.',
            'status.in' => 'The task status must be one of: ' . implode(', ', array_column(TaskStatus::cases(), 'value')) . '.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after' => 'The due date must be a future date.',
            'assignee_id.exists' => 'The selected assignee does not exist.',
            'children.array' => 'Children must be an array.',
            'children.*.title.required' => 'Each child task title is required.',
            'children.*.title.max' => 'Each child task title may not be greater than 255 characters.',
            'children.*.status.required' => 'Each child task status is required.',
            'children.*.status.in' => 'Each child task status must be one of: ' . implode(', ', array_column(TaskStatus::cases(), 'value')) . '.',
            'children.*.due_date.date' => 'Each child task due date must be a valid date.',
            'children.*.due_date.after' => 'Each child task due date must be a future date.',
            'children.*.assignee_id.exists' => 'The selected assignee for child task does not exist.'
        ];
    }
}
