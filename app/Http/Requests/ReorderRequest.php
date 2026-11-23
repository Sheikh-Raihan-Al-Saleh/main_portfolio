<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A new order for one sortable admin list: the IDs, first to last.
 * The list type comes from the route and is constrained there.
 */
class ReorderRequest extends FormRequest
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
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['integer', 'distinct'],
        ];
    }
}
