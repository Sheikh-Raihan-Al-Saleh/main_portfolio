<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'headline' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:250'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:120'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'url', 'max:255'],
            // The hero turns this into a "years in business" figure, so a year
            // is the only thing that can be stored here.
            'founded_year' => ['nullable', 'string', 'max:20', 'regex:/^\d{4}$/'],
            'hero_eyebrow' => ['nullable', 'string', 'max:80'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_statement' => ['nullable', 'string', 'max:255'],
            'primary_cta_label' => ['nullable', 'string', 'max:60'],
            'primary_cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_cta_label' => ['nullable', 'string', 'max:60'],
            'secondary_cta_url' => ['nullable', 'string', 'max:255'],
            'status_text' => ['nullable', 'string', 'max:120'],
            'accepting_projects' => ['boolean'],
            'socials' => ['nullable', 'array'],
            'socials.github' => ['nullable', 'url', 'max:255'],
            'socials.linkedin' => ['nullable', 'url', 'max:255'],
            'socials.x' => ['nullable', 'url', 'max:255'],
            'socials.website' => ['nullable', 'url', 'max:255'],
            'footer' => ['nullable', 'array'],
            'footer.status_text' => ['nullable', 'string', 'max:120'],
            'footer.status_text_unavailable' => ['nullable', 'string', 'max:120'],
            'footer.copyright' => ['nullable', 'string', 'max:255'],
            'footer.back_to_top' => ['nullable', 'string', 'max:80'],
            'footer.columns' => ['nullable', 'array', 'max:6'],
            'footer.columns.*.title' => ['required', 'string', 'max:120'],
            'footer.columns.*.links' => ['nullable', 'array', 'max:12'],
            'footer.columns.*.links.*.label' => ['required', 'string', 'max:120'],
            'footer.columns.*.links.*.url' => ['nullable', 'string', 'max:255'],
            'footer.legal_links' => ['nullable', 'array', 'max:12'],
            'footer.legal_links.*.label' => ['required', 'string', 'max:120'],
            'footer.legal_links.*.url' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'remove_logo' => ['boolean'],
            'remove_og_image' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'founded_year.regex' => 'Enter a four digit year, for example 2019.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'accepting_projects' => $this->boolean('accepting_projects'),
            'remove_logo' => $this->boolean('remove_logo'),
            'remove_og_image' => $this->boolean('remove_og_image'),
        ]);
    }
}
