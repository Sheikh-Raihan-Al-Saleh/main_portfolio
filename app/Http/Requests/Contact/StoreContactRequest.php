<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A public contact-form enquiry, from the studio site or the founder page.
 */
class StoreContactRequest extends FormRequest
{
    /**
     * Anyone may send the public contact form.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site' => ['nullable', Rule::in(['studio', 'founder'])],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            // Honeypot: bots fill every field, humans never see this one.
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.min' => 'Please add a little more detail (at least 20 characters).',
            'website.prohibited' => 'Your submission could not be processed.',
        ];
    }
}
