<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * The studio's clients and partners.
 */
class ClientService
{
    public function __construct(
        private readonly MediaService $media,
        private readonly StudioService $studios,
    ) {}

    /**
     * Visible clients for the public logo strip.
     *
     * @return Collection<int, Client>
     */
    public function visible(): Collection
    {
        return $this->studios->current()->clients()->visible()->ordered()->get();
    }

    /**
     * @return Collection<int, Client>
     */
    public function adminIndex(?string $search): Collection
    {
        return $this->studios->current()->clients()
            ->when(filled($search), fn (Builder $query) => $query->where(
                fn (Builder $inner) => $inner->whereLike('name', "%{$search}%")
                    ->orWhereLike('industry', "%{$search}%"),
            ))
            ->ordered()
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function create(array $data): Client
    {
        $studio = $this->studios->current();

        $client = new Client($this->attributes($data));
        $client->sort_order = (int) $studio->clients()->max('sort_order') + 1;
        $client->logo_path = isset($data['logo']) ? $this->media->store($data['logo'], 'clients') : null;

        $studio->clients()->save($client);

        return $client;
    }

    /**
     * @param  array<string, mixed>  $data  Validated form data (files included).
     */
    public function update(Client $client, array $data): Client
    {
        $client->fill($this->attributes($data));
        $client->logo_path = $this->media->replace(
            $data['logo'] ?? null,
            'clients',
            $client->logo_path,
            (bool) ($data['remove_logo'] ?? false),
        );
        $client->save();

        return $client;
    }

    public function delete(Client $client): void
    {
        $client->delete();
        $this->media->delete($client->logo_path);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return collect($data)->only(['name', 'industry', 'summary', 'website_url', 'is_visible'])->all();
    }
}
