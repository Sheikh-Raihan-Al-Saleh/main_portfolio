<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientRequest;
use App\Models\Client;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Manages the client and partner logos behind the "trusted by" strip on the
 * company home page and the About page.
 *
 * Clients are company records rather than landing sections: the same logo is
 * reused across pages, and it outlives any single project.
 */
class ClientController extends Controller
{
    use HandlesMediaUploads;

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        return inertia('admin/clients/Index', [
            'clients' => Company::current()->clients()
                ->when($search !== '', fn ($query) => $query->where(
                    fn ($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('industry', 'like', "%{$search}%"),
                ))
                ->ordered()
                ->get(),
            'filters' => ['search' => $search ?: null],
        ]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = Company::current()->clients()->create([
            ...$request->safe()->only(['name', 'industry', 'summary', 'website_url', 'is_visible']),
            // Append to the end of the strip; an empty table starts at zero.
            'sort_order' => (int) (Client::query()->max('sort_order') ?? -1) + 1,
        ]);

        $client->logo_path = $this->storeMedia($request->file('logo'), 'clients');
        $client->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client added.')]);

        return back();
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $client->fill($request->safe()->only([
            'name', 'industry', 'summary', 'website_url', 'is_visible',
        ]));

        if ($request->boolean('remove_logo')) {
            $this->deleteMedia($client->logo_path);
            $client->logo_path = null;
        }

        $client->logo_path = $this->storeMedia(
            $request->file('logo'), 'clients', $client->logo_path,
        );

        $client->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Client updated.')]);

        return back();
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->deleteMedia($client->logo_path);
        $client->delete();

        Inertia::flash('toast', ['type' => 'error', 'message' => __('Client removed.')]);

        return back();
    }

    /**
     * Persist a new drag-and-drop ordering for the logo strip.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:clients,id'],
        ]);

        foreach ($validated['ids'] as $position => $id) {
            Client::query()->whereKey($id)->update(['sort_order' => $position]);
        }

        return back();
    }
}
