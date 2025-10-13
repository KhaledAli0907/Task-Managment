<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskDependencyRequest extends FormRequest
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
            'dependency_task_id' => [
                'required',
                'string',
                'exists:tasks,id',
                function ($attribute, $value, $fail) {
                    $taskId = $this->route('id');
                    if ($value === $taskId) {
                        $fail('A task cannot depend on itself.');
                    }
                },
            ]
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
            'dependency_task_id.required' => 'The dependency task ID is required.',
            'dependency_task_id.exists' => 'The selected dependency task does not exist.',
            'dependency_task_id.different' => 'A task cannot depend on itself.'
        ];
    }
}
