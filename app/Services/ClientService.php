<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Studio;
use Illuminate\Support\Arr;

/**
 * Creates, updates and deletes the studio's clients with their logo files.
 */
class ClientService
{
    /** Form fields saved to the table. */
    private const FIELDS = ['name', 'industry', 'summary', 'website_url', 'is_visible'];

    public function __construct(private FileService $files) {}

    /**
     * Add a client at the end of the logo strip, with its logo.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function create(array $data): Client
    {
        $studio = Studio::current();

        $client = new Client(Arr::only($data, self::FIELDS));
        $client->sort_order = (int) $studio->clients()->max('sort_order') + 1;
        $client->logo_path = isset($data['logo']) ? $this->files->store($data['logo'], 'clients') : null;
        $studio->clients()->save($client);

        return $client;
    }

    /**
     * Save the changes to a client; a new logo replaces the old file.
     *
     * @param  array<string, mixed>  $data  Validated form data.
     */
    public function update(Client $client, array $data): void
    {
        $client->fill(Arr::only($data, self::FIELDS));
        $client->logo_path = $this->files->replace(
            $data['logo'] ?? null,
            'clients',
            $client->logo_path,
            (bool) ($data['remove_logo'] ?? false),
        );
        $client->save();
    }

    /**
     * Delete a client and its logo file.
     */
    public function delete(Client $client): void
    {
        $client->delete();
        $this->files->delete($client->logo_path);
    }
}
