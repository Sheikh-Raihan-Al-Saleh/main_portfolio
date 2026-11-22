<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafeSvg;
use Illuminate\Foundation\Http\FormRequest;

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
            // SVG is allowed (the seeded logos are SVGs), so formats are listed by
            // extension: the `image` rule rejects svg outright. Uploads are served
            // from this origin, so SafeSvg refuses anything beyond plain shapes.
            'logo' => ['nullable', 'mimes:png,jpg,jpeg,gif,webp,svg', 'max:2048', new SafeSvg],
            'remove_logo' => ['boolean'],
            'is_visible' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_visible' => $this->boolean('is_visible'),
            'remove_logo' => $this->boolean('remove_logo'),
        ]);
    }
}
