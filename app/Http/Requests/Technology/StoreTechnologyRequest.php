<?php

namespace App\Http\Requests\Technology;

use App\Models\Technology;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class StoreTechnologyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:60'],
            'icon' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * Names are unique by slug ("Vue.js" and "vue js" are the same technology).
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $current = $this->route('technology');
                $ignoreId = $current instanceof Technology ? $current->id : null;

                $taken = Technology::query()
                    ->where('slug', Str::slug((string) $this->input('name')))
                    ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('name', __('This technology already exists.'));
                }
            },
        ];
    }
}
