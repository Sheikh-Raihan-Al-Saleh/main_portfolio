<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_statement' => ['nullable', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:250'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'founder_message' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:120'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'available_for_work' => ['boolean'],
            'roles' => ['nullable', 'array', 'max:6'],
            'roles.*' => ['string', 'max:80'],
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
            'content' => ['nullable', 'array'],
            'content.sections' => ['nullable', 'array'],
            'content.sections.*.eyebrow' => ['nullable', 'string', 'max:120'],
            'content.sections.*.title' => ['nullable', 'string', 'max:180'],
            'content.sections.*.highlight' => ['nullable', 'string', 'max:120'],
            'content.sections.*.description' => ['nullable', 'string', 'max:300'],
            'content.sections.about.message_label' => ['nullable', 'string', 'max:80'],
            'content.hero.primary_cta_label' => ['nullable', 'string', 'max:60'],
            'content.hero.primary_cta_url' => ['nullable', 'string', 'max:120'],
            'content.hero.secondary_cta_label' => ['nullable', 'string', 'max:60'],
            'content.hero.secondary_cta_url' => ['nullable', 'string', 'max:120'],
            'content.hero.resume_label' => ['nullable', 'string', 'max:60'],
            'content.hero.years_label' => ['nullable', 'string', 'max:40'],
            'content.hero.projects_label' => ['nullable', 'string', 'max:40'],
            'content.hero.skills_label' => ['nullable', 'string', 'max:40'],
            'content.hero.scroll_label' => ['nullable', 'string', 'max:40'],
            'content.about.role_label' => ['nullable', 'string', 'max:40'],
            'content.about.location_label' => ['nullable', 'string', 'max:40'],
            'content.about.email_label' => ['nullable', 'string', 'max:40'],
            'content.about.phone_label' => ['nullable', 'string', 'max:40'],
            'content.about.available_open' => ['nullable', 'string', 'max:120'],
            'content.about.available_closed' => ['nullable', 'string', 'max:120'],
            'content.contact.toast_title' => ['nullable', 'string', 'max:80'],
            'content.contact.toast_description' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
            'remove_avatar' => ['boolean'],
            'remove_logo' => ['boolean'],
            'remove_og_image' => ['boolean'],
            'remove_resume' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'available_for_work' => $this->boolean('available_for_work'),
            'remove_avatar' => $this->boolean('remove_avatar'),
            'remove_logo' => $this->boolean('remove_logo'),
            'remove_og_image' => $this->boolean('remove_og_image'),
            'remove_resume' => $this->boolean('remove_resume'),
        ]);
    }
}
