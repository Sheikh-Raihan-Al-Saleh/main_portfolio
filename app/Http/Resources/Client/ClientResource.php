<?php

namespace App\Http\Resources\Client;

use App\Models\Client;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A client as the admin list and the public logo strip see it: every column
 * plus the public URL of its logo.
 *
 * @mixin Client
 */
class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Every column, then every loaded relation (what Model::toArray() returns).
            ...$this->attributesToArray(),
            ...$this->relationsToArray(),
            'logo_url' => FileService::url($this->logo_path),
        ];
    }
}
