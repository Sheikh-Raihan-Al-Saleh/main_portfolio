<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\SiteFormRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The admin "Founder" form: identity, contact, media, hero, page copy, footer, SEO.
 */
class FounderRequest extends FormRequest
{
    use SiteFormRules;

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
            // Identity
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:250'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'founder_message' => ['nullable', 'string', 'max:5000'],
            'roles' => ['nullable', 'array', 'max:6'],
            'roles.*' => ['string', 'max:80'],
            'is_available_for_work' => ['boolean'],

            // Contact
            'location' => ['nullable', 'string', 'max:120'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],

            // Media
            'avatar' => ['nullable', 'image', 'max:2048'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
            'remove_avatar' => ['boolean'],
            'remove_logo' => ['boolean'],
            'remove_resume' => ['boolean'],

            // Hero
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_statement' => ['nullable', 'string', 'max:255'],
            'primary_cta_label' => ['nullable', 'string', 'max:60'],
            'primary_cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_cta_label' => ['nullable', 'string', 'max:60'],
            'secondary_cta_url' => ['nullable', 'string', 'max:255'],

            // Page copy (merged over FounderDefaults::content() when read)
            'content' => ['nullable', 'array'],
            'content.sections' => ['nullable', 'array'],
            'content.sections.*.eyebrow' => ['nullable', 'string', 'max:120'],
            'content.sections.*.title' => ['nullable', 'string', 'max:180'],
            'content.sections.*.highlight' => ['nullable', 'string', 'max:120'],
            'content.sections.*.description' => ['nullable', 'string', 'max:300'],
            'content.sections.about.message_label' => ['nullable', 'string', 'max:80'],
            'content.hero' => ['nullable', 'array'],
            'content.hero.*' => ['nullable', 'string', 'max:60'],
            'content.about' => ['nullable', 'array'],
            'content.about.*' => ['nullable', 'string', 'max:120'],

            ...$this->socialRules(),
            ...$this->footerRules(),
            ...$this->seoRules(),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_available_for_work' => $this->boolean('is_available_for_work'),
            'remove_avatar' => $this->boolean('remove_avatar'),
            'remove_logo' => $this->boolean('remove_logo'),
            'remove_resume' => $this->boolean('remove_resume'),
            'remove_og_image' => $this->boolean('remove_og_image'),
        ]);
    }
}
