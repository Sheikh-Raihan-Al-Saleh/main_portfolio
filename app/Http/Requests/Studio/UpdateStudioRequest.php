<?php

namespace App\Http\Requests\Studio;

use App\Http\Requests\Concerns\SiteFormRules;
use App\Rules\SafeSvg;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The admin "Studio" form: brand (name, logos), story, contact, hero, footer, SEO.
 */
class UpdateStudioRequest extends FormRequest
{
    use SiteFormRules;

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
        // Logos may be SVG. The `image` rule rejects SVG, so formats are listed
        // by extension, and SafeSvg refuses anything beyond plain shapes.
        $logo = ['nullable', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048', new SafeSvg];

        return [
            // Brand
            'name' => ['required', 'string', 'max:120'],
            'short_name' => ['nullable', 'string', 'max:60'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'logo' => $logo,
            'logo_dark' => $logo,
            // The mark doubles as the favicon, so it must be square. SVG has no
            // pixel size to check, so the rule only applies to raster files.
            'logo_mark' => $this->isSvg('logo_mark') ? $logo : [...$logo, 'dimensions:ratio=1'],
            'remove_logo' => ['boolean'],
            'remove_logo_dark' => ['boolean'],
            'remove_logo_mark' => ['boolean'],

            // Story
            'headline' => ['nullable', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:250'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:2000'],
            'founded_year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.now()->year],
            'is_accepting_projects' => ['boolean'],

            // Contact
            'location' => ['nullable', 'string', 'max:120'],
            'public_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'website_url' => ['nullable', 'url', 'max:255'],

            // Hero
            'hero_eyebrow' => ['nullable', 'string', 'max:80'],
            'hero_title' => ['nullable', 'string', 'max:255'],
            'hero_statement' => ['nullable', 'string', 'max:255'],
            'primary_cta_label' => ['nullable', 'string', 'max:60'],
            'primary_cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_cta_label' => ['nullable', 'string', 'max:60'],
            'secondary_cta_url' => ['nullable', 'string', 'max:255'],

            ...$this->socialRules(),
            ...$this->footerRules(),
            ...$this->seoRules(),
        ];
    }

    /**
     * Whether the uploaded file under this key is an SVG (checked by extension).
     */
    private function isSvg(string $key): bool
    {
        $file = $this->file($key);

        return $file !== null && strtolower($file->getClientOriginalExtension()) === 'svg';
    }

    /**
     * Turn checkbox values ("on", "1", missing) into real booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_accepting_projects' => $this->boolean('is_accepting_projects'),
            'remove_logo' => $this->boolean('remove_logo'),
            'remove_logo_dark' => $this->boolean('remove_logo_dark'),
            'remove_logo_mark' => $this->boolean('remove_logo_mark'),
            'remove_og_image' => $this->boolean('remove_og_image'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'founded_year.digits' => 'Enter a four digit year, for example 2019.',
            'founded_year.max' => 'The founding year cannot be in the future.',
            'logo_mark.dimensions' => 'The logo mark must be square (it is also the browser tab icon).',
        ];
    }
}
