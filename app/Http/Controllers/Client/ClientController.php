<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Requests\ReorderRequest;
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

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('admin/clients/Index', [
            'clients' => $this->clientService->list($search),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $this->clientService->create($request->validated());
        $this->toast(__('Client added.'));

        return back();
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clientService->update($client, $request->validated());
        $this->toast(__('Client updated.'));

        return back();
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->clientService->delete($client);
        $this->toast(__('Client removed.'), 'error');

        return back();
    }

    public function reorder(ReorderRequest $request): RedirectResponse
    {
        $this->clientService->reorder($request->validated('ids'));

        return back();
    }
}
