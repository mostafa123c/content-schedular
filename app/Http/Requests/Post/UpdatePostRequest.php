<?php

namespace App\Http\Requests\Post;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'image_url' => 'nullable|image|max:2048',
            'scheduled_time' => 'sometimes|required|date|after:now',
            'platforms' => 'sometimes|required|array|min:1',
            'platforms.*' => 'exists:platforms,id',
            'status' => 'sometimes|required|in:' . implode(',', [Post::STATUS_DRAFT, Post::STATUS_SCHEDULED]),
        ];
    }
}