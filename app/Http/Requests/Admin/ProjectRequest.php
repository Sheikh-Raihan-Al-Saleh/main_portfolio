<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
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
        $projectId = $this->currentProject()?->id;

        return [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'nullable', 'string', 'max:200', 'alpha_dash',
                Rule::unique('projects', 'slug')->ignore($projectId),
            ],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:20000'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'remove_cover_image' => ['boolean'],
            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['image', 'max:4096'],
            'tech_stack' => ['nullable', 'array', 'max:30'],
            'tech_stack.*' => ['string', 'max:40'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'role' => ['nullable', 'string', 'max:120'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }

    /**
     * Model attributes only -- files and UI-only flags are handled by the controller.
     *
     * @return array<string, mixed>
     */
    public function projectAttributes(): array
    {
        $data = $this->safe()->only([
            'title', 'summary', 'description', 'tech_stack', 'repo_url',
            'live_url', 'role', 'is_featured', 'is_published', 'started_at', 'completed_at',
        ]);

        $slug = $this->safe()->string('slug')->trim()->value();
        $ignoreId = $this->currentProject()?->id;

        $data['slug'] = Project::uniqueSlug($slug !== '' ? $slug : $data['title'], $ignoreId);

        return $data;
    }

    /**
     * The project being updated, or null when storing a new one.
     */
    private function currentProject(): ?Project
    {
        $project = $this->route('project');

        return $project instanceof Project ? $project : null;
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
