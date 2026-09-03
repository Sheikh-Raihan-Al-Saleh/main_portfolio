<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LandingPageRequest extends FormRequest
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
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'headline' => ['nullable', 'string', 'max:180'],
            'subheadline' => ['nullable', 'string', 'max:600'],
            'hero_media' => ['nullable', 'image', 'max:6144'],
            'remove_hero_media' => ['boolean'],
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
            'og_image' => ['nullable', 'image', 'max:4096'],
            'remove_og_image' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }

    /**
     * Model attributes only -- files and UI-only flags stay with the controller.
     *
     * @return array<string, mixed>
     */
    public function landingAttributes(): array
    {
        return $this->safe()->only([
            'eyebrow', 'headline', 'subheadline', 'hero_video_url',
            'primary_cta_label', 'primary_cta_url',
            'secondary_cta_label', 'secondary_cta_url',
            'accent_from', 'accent_to', 'seo_title', 'seo_description',
            'is_published',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'remove_hero_media' => $this->boolean('remove_hero_media'),
            'remove_og_image' => $this->boolean('remove_og_image'),
        ]);
    }
}
