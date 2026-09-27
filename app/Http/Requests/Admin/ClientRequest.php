<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class ClientRequest extends FormRequest
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
            'industry' => ['nullable', 'string', 'max:80'],
            'summary' => ['nullable', 'string', 'max:300'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'mimes:png,jpg,jpeg,gif,webp,svg', 'max:2048'],
            'remove_logo' => ['boolean'],
            'is_visible' => ['boolean'],
        ];
    }

    /**
     * The admin is told SVG is accepted, and the seeded logos are SVGs, so the
     * rule allows it. The formats are listed by extension rather than with the
     * `image` rule, which rejects svg outright.
     *
     * Uploaded files are served from this origin, and an SVG is a document that
     * can carry script, so anything scriptable is refused. This is a guard
     * against accidents rather than a full sanitizer: real logo files are
     * plain shapes and text.
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $file = $this->file('logo');

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                return;
            }

            if ($file->getMimeType() !== 'image/svg+xml') {
                return;
            }

            $contents = (string) file_get_contents($file->getRealPath());

            if (preg_match('/<\s*script|<\s*foreignObject|javascript:|<\s*handler|\son[a-z]+\s*=/i', $contents) === 1) {
                $validator->errors()->add('logo', 'The logo may not contain scripts or embedded code.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_visible' => $this->boolean('is_visible'),
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }
}
