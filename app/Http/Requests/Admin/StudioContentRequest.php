<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudioContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * The content payload is a free-form JSON object edited by the section
     * editor; per-field validation lives in the editor's own required-field
     * checks, and every value is a string, a list of strings or a list of
     * small objects, so the shape is guarded here rather than per key.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'array'],
            'content.*' => ['array'],
        ];
    }

    /**
     * The editor submits the payload as one hidden input containing JSON, so
     * a string arrives here; decode it before the `array` rules run.
     */
    protected function prepareForValidation(): void
    {
        $content = $this->input('content');

        if (is_string($content)) {
            $decoded = json_decode($content, true);

            $this->merge([
                'content' => is_array($decoded) ? $decoded : null,
            ]);
        }
    }
}
