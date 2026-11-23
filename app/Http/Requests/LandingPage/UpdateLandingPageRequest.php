<?php

namespace App\Http\Requests\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the landing page builder's page settings form.
 */
class UpdateLandingPageRequest extends FormRequest
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
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'headline' => ['nullable', 'string', 'max:180'],
            'subheadline' => ['nullable', 'string', 'max:600'],
            'hero_video_url' => ['nullable', 'url', 'max:2048'],
            'primary_cta_label' => ['nullable', 'string', 'max:80'],
            'primary_cta_url' => ['nullable', 'string', 'max:2048'],
            'secondary_cta_label' => ['nullable', 'string', 'max:80'],
            'secondary_cta_url' => ['nullable', 'string', 'max:2048'],
            // Hex colours drive the page's gradient identity.
            'accent_from' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_to' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:300'],
            'is_published' => ['boolean'],

            'og_image' => ['nullable', 'image', 'max:4096'],
            'remove_og_image' => ['boolean'],

            // Hero screenshots by slot: 0 desktop, 1 tablet, 2 phone.
            'hero_media' => ['nullable', 'array', 'max:3'],
            'hero_media.*' => ['nullable', 'image', 'max:6144'],
            'remove_hero_media' => ['nullable', 'array', 'max:3'],
            'remove_hero_media.*' => ['boolean'],
        ];
    }

    /**
     * Turn checkbox values ("on", "1", missing) into real booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'remove_og_image' => $this->boolean('remove_og_image'),
        ]);
    }
}
