<?php

namespace App\Http\Requests\Admin;

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LandingSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Base rules plus whatever the resolved section type declares for its
     * `data` payload, so each block validates its own shape.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'heading' => ['nullable', 'string', 'max:180'],
            'subheading' => ['nullable', 'string', 'max:400'],
            'body' => ['nullable', 'string', 'max:4000'],
            'is_visible' => ['boolean'],
            'data' => ['required', 'array'],
        ];

        if ($this->isMethod('POST')) {
            // The type is fixed at creation: changing it later would leave the
            // stored payload in the wrong shape.
            $rules['type'] = ['required', Rule::enum(LandingSectionType::class)];

            // A section belongs to exactly one owner -- a project's landing
            // page, or the company home page -- so require one. Supplying both
            // is rejected by the after-callback below, which can report a
            // clearer message than the built-in rules allow.
            $rules['landing_page_id'] = [
                'nullable', 'integer', 'exists:project_landing_pages,id',
                'required_without:company_id',
            ];
            $rules['company_id'] = [
                'nullable', 'integer', 'exists:companies,id',
                'required_without:landing_page_id',
            ];
        }

        return array_merge($rules, $this->sectionType()?->rules() ?? []);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'data.url.required' => 'A demo URL is required.',
        ];
    }

    /**
     * The type being validated: from the payload when creating, from the
     * existing record when updating.
     */
    public function sectionType(): ?LandingSectionType
    {
        $section = $this->route('landing_section');

        if ($section instanceof LandingSection) {
            return $section->type;
        }

        return LandingSectionType::tryFrom((string) $this->input('type'));
    }

    /**
     * Model attributes only. `data` is deliberately taken from the validated
     * set so unknown keys never reach the JSON column.
     *
     * @return array<string, mixed>
     */
    public function sectionAttributes(): array
    {
        $attributes = $this->safe()->only([
            'eyebrow', 'heading', 'subheading', 'body', 'is_visible', 'data',
        ]);

        if ($this->isMethod('POST')) {
            // The owner is set once, at creation, and is deliberately not
            // accepted on update so a section cannot be moved between pages.
            $attributes['type'] = $this->sectionType();
            $attributes['landing_page_id'] = $this->safe()->input('landing_page_id');
            $attributes['company_id'] = $this->safe()->input('company_id');
        }

        return $attributes;
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->isMethod('POST') && $this->filled('landing_page_id') && $this->filled('company_id')) {
                $message = 'A section belongs to either a landing page or the company, not both.';

                $validator->errors()->add('landing_page_id', $message);
                $validator->errors()->add('company_id', $message);
            }

            if ($this->sectionType() !== LandingSectionType::DemoEmbed) {
                return;
            }

            $url = (string) $this->input('data.url');

            if ($url === '') {
                return;
            }

            $scheme = parse_url($url, PHP_URL_SCHEME);
            $host = parse_url($url, PHP_URL_HOST);

            // A framed demo runs in the visitor's browser; refusing plain http
            // keeps it from being a mixed-content or tampering vector.
            if ($scheme !== 'https') {
                $validator->errors()->add('data.url', 'The demo URL must use https.');

                return;
            }

            /** @var array<int, string> $allowed */
            $allowed = config('portfolio.embed_hosts', []);

            if ($allowed === [] || ! is_string($host)) {
                return;
            }

            foreach ($allowed as $allowedHost) {
                $allowedHost = trim($allowedHost);

                if ($host === $allowedHost || str_ends_with($host, '.'.$allowedHost)) {
                    return;
                }
            }

            $validator->errors()->add(
                'data.url',
                'That host is not in the allowed embed list.',
            );
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_visible' => $this->boolean('is_visible'),
        ]);
    }
}
