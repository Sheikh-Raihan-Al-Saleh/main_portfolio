<?php

namespace App\Http\Requests\Skill;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the admin skill form (create; the update request extends it).
 */
class StoreSkillRequest extends FormRequest
{
    /**
     * Only an admin may send this form.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * The category is checked against the founder's own categories in
     * SkillService, so only the shape is validated here.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'skill_category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:80'],
            'proficiency' => ['required', 'integer', 'min:0', 'max:100'],
            'icon' => ['nullable', 'string', 'max:40'],
            'is_featured' => ['boolean'],
        ];
    }

    /**
     * Turn checkbox values ("on", "1", missing) into real booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_featured' => $this->boolean('is_featured')]);
    }
}
