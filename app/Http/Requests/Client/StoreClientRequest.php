<?php

namespace App\Http\Requests\Client;

use App\Rules\SafeSvg;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the admin client form (create; the update request extends it).
 */
class StoreClientRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'industry' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:500'],
            'website_url' => ['nullable', 'url', 'max:255'],
            // SVG is allowed (the seeded logos are SVGs), so formats are listed by
            // extension: the `image` rule rejects svg outright. Uploads are served
            // from this origin, so SafeSvg refuses anything beyond plain shapes.
            'logo' => ['nullable', 'mimes:png,jpg,jpeg,gif,webp,svg', 'max:2048', new SafeSvg],
            'remove_logo' => ['boolean'],
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * Turn checkbox values ("on", "1", missing) into real booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_visible' => $this->boolean('is_visible'),
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }
}
