<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class JobLogUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'sync_id' => ['required', 'string', 'max:255', 'unique:job_logs,sync_id'],
            'status' => ['required', 'string', 'in:completed,failed,cancelled'],
            'started_at' => ['required', 'date', 'before_or_equal:completed_at'],
            'completed_at' => ['required', 'date', 'after_or_equal:started_at'],
            'latitude_start' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_start' => ['nullable', 'numeric', 'between:-180,180'],
            'latitude_end' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_end' => ['nullable', 'numeric', 'between:-180,180'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'photo_base64' => ['nullable', 'string', 'max:10485760'], // 10MB max
            'metadata' => ['nullable', 'array'],
            'metadata.task_type' => ['nullable', 'string', 'max:100'],
            'metadata.customer_signature' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'location_id.required' => 'Location ID is required.',
            'location_id.exists' => 'The specified location does not exist.',
            'sync_id.unique' => 'This job log has already been uploaded.',
            'status.in' => 'Status must be one of: completed, failed, cancelled.',
            'started_at.before_or_equal' => 'Start time must be before or equal to completion time.',
            'completed_at.after_or_equal' => 'Completion time must be after or equal to start time.',
            'photo_base64.max' => 'Photo size must not exceed 10MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422));
    }
}