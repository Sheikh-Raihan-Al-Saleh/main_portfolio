<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ExperienceRequest extends FormRequest
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
        return [
            'company' => ['required', 'string', 'max:150'],
            'role' => ['required', 'string', 'max:150'],
            'employment_type' => ['nullable', 'string', 'max:60'],
            'location' => ['nullable', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string', 'max:5000'],
            'highlights' => ['nullable', 'array', 'max:12'],
            'highlights.*' => ['string', 'max:300'],
            'company_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['remove_logo' => $this->boolean('remove_logo')]);
    }
}
