<?php

namespace App\Http\Requests\LandingPage;

use Illuminate\Foundation\Http\FormRequest;

/**
 * One image or video uploaded for a content block (up to 50 MB).
 */
class UploadLandingMediaRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif,gif,mp4,webm', 'max:51200'],
            'directory' => ['required', 'string', 'in:steps,gallery,video,poster,avatar'],
        ];
    }
}
