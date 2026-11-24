<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Requests\ReorderRequest;
use App\Http\Resources\Client\ClientResource;
use App\Models\Client;
use App\Models\Studio;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the studio's clients. Created and edited in dialogs on the list page.
 */
class ClientController extends Controller
{
    public function __construct(private ClientService $clients) {}

    /**
     * Show the list of clients, optionally filtered by name or industry.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $query = Studio::current()->clients()->ordered();

        if ($search !== '') {
            $query->whereAny(['name', 'industry'], 'like', "%{$search}%");
        }

        $clients = $query->get();

        return Inertia::render('client/ClientIndex', [
            'clients' => ClientResource::collection($clients)->resolve(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    /**
     * Save a new client at the end of the strip, with its logo.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->clients->create($request->validated());

        $this->toast(__('Client added.'));

        return back();
    }

    /**
     * Save the changes to a client; a new logo replaces the old file.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clients->update($client, $request->validated());

        $this->toast(__('Client updated.'));

        return back();
    }

    /**
     * Delete a client and its logo file.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->clients->delete($client);

        $this->toast(__('Client removed.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the clients.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->saveSortOrder(Studio::current()->clients(), $request->validated('ids'));

        return back();
    }
}
