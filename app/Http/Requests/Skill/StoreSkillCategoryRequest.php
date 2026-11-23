<?php

namespace App\Http\Requests\Skill;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the skill category name (create; the update request extends it).
 */
class StoreSkillCategoryRequest extends FormRequest
{
    /**
     * Only an admin may send this form.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
        ];
    }
}
