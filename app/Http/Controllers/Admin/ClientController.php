<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The studio's clients. Created and edited in dialogs on the index page.
 */
class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clients,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return Inertia::render('admin/clients/Index', [
            'clients' => $this->clients->adminIndex($search),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $this->clients->create($request->validated());

        $this->toast(__('Client added.'));

        return back();
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $this->clients->update($client, $request->validated());

        $this->toast(__('Client updated.'));

        return back();
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->clients->delete($client);

        $this->toast(__('Client removed.'), 'error');

        return back();
    }
}
