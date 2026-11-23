<?php

namespace App\Http\Requests\LandingPage;

use App\Enums\LandingSectionType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A new content block: its type, the page it goes on, and its fields.
 * Each block type adds the rules for its own `data` (LandingSectionType).
 */
class StoreLandingSectionRequest extends FormRequest
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
            'type' => ['required', Rule::enum(LandingSectionType::class)],
            // The studio home page, or a project's landing page.
            'owner' => ['required', Rule::in(['studio', 'project'])],
            'project_id' => ['required_if:owner,project', 'nullable', 'integer', 'exists:projects,id'],
            ...$this->blockRules(),
        ];
    }

    /**
     * The block's type: from the form when creating.
     */
    public function sectionType(): ?LandingSectionType
    {
        return LandingSectionType::tryFrom((string) $this->input('type'));
    }

    /**
     * Rules shared by store and update: the common fields plus the fields of
     * the block's type.
     *
     * @return array<string, mixed>
     */
    protected function blockRules(): array
    {
        return [
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'heading' => ['nullable', 'string', 'max:180'],
            'subheading' => ['nullable', 'string', 'max:400'],
            'body' => ['nullable', 'string', 'max:4000'],
            'is_visible' => ['boolean'],
            'data' => ['required', 'array'],
            ...($this->sectionType()?->rules() ?? []),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['data.url.required' => 'A demo URL is required.'];
    }

    /**
     * A live demo is framed on the site, so it must use https, and (when a
     * list is configured) come from an allowed host.
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $url = (string) $this->input('data.url');

            if ($this->sectionType() !== LandingSectionType::DemoEmbed || $url === '') {
                return;
            }

            if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
                $validator->errors()->add('data.url', 'The demo URL must use https.');

                return;
            }

            /** @var array<int, string> $allowedHosts */
            $allowedHosts = config('portfolio.embed_hosts', []);
            $host = (string) parse_url($url, PHP_URL_HOST);

            if ($allowedHosts === []) {
                return;
            }

            foreach ($allowedHosts as $allowed) {
                if ($host === trim($allowed) || str_ends_with($host, '.'.trim($allowed))) {
                    return;
                }
            }

            $validator->errors()->add('data.url', 'That host is not in the allowed embed list.');
        });
    }

    /**
     * Turn checkbox values ("on", "1", missing) into real booleans before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_visible' => $this->boolean('is_visible')]);
    }
}
