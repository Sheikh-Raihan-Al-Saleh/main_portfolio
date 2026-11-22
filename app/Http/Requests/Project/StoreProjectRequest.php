<?php

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'owner' => ['required', Rule::in(['studio', 'founder'])],
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('projects', 'slug')->ignore($project instanceof Project ? $project->id : null),
            ],
            'summary' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'role' => ['nullable', 'string', 'max:120'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],

            'cover_image' => ['nullable', 'image', 'max:4096'],
            'remove_cover_image' => ['boolean'],

            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['image', 'max:4096'],
            // IDs of existing project_images rows to delete.
            'remove_gallery' => ['nullable', 'array'],
            'remove_gallery.*' => ['integer'],

            // Technology names; new ones are added to the shared vocabulary.
            'tech_stack' => ['nullable', 'array', 'max:30'],
            'tech_stack.*' => ['string', 'max:60'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured'),
            'is_published' => $this->boolean('is_published'),
            'remove_cover_image' => $this->boolean('remove_cover_image'),
        ]);
    }
}
