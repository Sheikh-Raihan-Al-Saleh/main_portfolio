<?php

namespace App\Services\Client;

use App\Models\Client;
use App\Services\Media\MediaService;
use App\Services\Studio\StudioService;
use App\Support\SortOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * Client module: the studio's clients and partners.
 */
class ClientService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'industry', 'summary', 'website_url', 'is_visible'];

    public function __construct(
        private readonly MediaService $mediaService,
        private readonly StudioService $studioService,
    ) {}

    /**
     * Visible clients, in order, for the public logo strip.
     *
     * @return Collection<int, Client>
     */
    public function visible(): Collection
    {
        return $this->studioService->current()->clients()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * All clients for the admin list, optionally filtered by name or industry.
     *
     * @return Collection<int, Client>
     */
    public function list(?string $search = null): Collection
    {
        return $this->studioService->current()->clients()
            ->when($search, fn (Builder $query) => $query->where(
                fn (Builder $searchQuery) => $searchQuery->whereLike('name', "%{$search}%")->orWhereLike('industry', "%{$search}%"),
            ))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a client, with its logo if one was uploaded.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Client
    {
        $studio = $this->studioService->current();

        $client = new Client(Arr::only($data, self::FIELDS));
        $client->sort_order = (int) $studio->clients()->max('sort_order') + 1;
        $client->logo_path = isset($data['logo']) ? $this->mediaService->store($data['logo'], 'clients') : null;
        $studio->clients()->save($client);

        return $client;
    }

    /**
     * Update a client; a new logo replaces the old file.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Client $client, array $data): Client
    {
        $client->fill(Arr::only($data, self::FIELDS));
        $client->logo_path = $this->mediaService->replace(
            $data['logo'] ?? null,
            'clients',
            $client->logo_path,
            (bool) ($data['remove_logo'] ?? false),
        );
        $client->save();

        return $client;
    }

    /**
     * Delete a client and its logo file.
     */
    public function delete(Client $client): void
    {
        $client->delete();
        $this->mediaService->delete($client->logo_path);
    }

    /**
     * Save a new order for the logo strip.
     *
     * @param  array<int, int>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        SortOrder::apply($this->studioService->current()->clients(), $orderedIds);
    }
}
