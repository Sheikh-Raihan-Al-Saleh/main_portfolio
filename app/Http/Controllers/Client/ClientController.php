<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Requests\ReorderRequest;
use App\Http\Resources\Client\ClientResource;
use App\Models\Client;
use App\Services\Client\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin: the studio's clients. Created and edited in dialogs on the list page.
 */
class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {}

    /**
     * Show the list of clients.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('admin/clients/Index', [
            'clients' => ClientResource::collection($this->clientService->list($search))->resolve(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    /**
     * Validate and save a new client. The service does the work.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->clientService->create($request->validated());
        $this->toast(__('Client added.'));

        return back();
    }

    /**
     * Validate and save the changes to a client. The service does the work.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clientService->update($client, $request->validated());
        $this->toast(__('Client updated.'));

        return back();
    }

    /**
     * Delete a client.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->clientService->delete($client);
        $this->toast(__('Client removed.'), 'error');

        return back();
    }

    /**
     * Save the new display order of the clients.
     */
    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->clientService->reorder($request->validated('ids'));

        return back();
    }
}
