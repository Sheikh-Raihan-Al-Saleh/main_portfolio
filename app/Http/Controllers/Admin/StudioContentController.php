<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudioContentRequest;
use App\Models\Company;
use App\Support\StudioContent;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Edits the copy of the studio home page's fixed sections: the numbered
 * chapters (what we build, engineering, why us) plus the closing CTA, ticker
 * and section headings. Composable blocks are edited separately on the
 * Company page; this is the copy baked into the page's own components.
 */
class StudioContentController extends Controller
{
    public function edit(): Response
    {
        $company = Company::current();

        return inertia('admin/StudioSections', [
            'company' => $company->only('id', 'name'),
            'content' => $company->studio_content,
        ]);
    }

    public function update(StudioContentRequest $request): RedirectResponse
    {
        $company = Company::current();

        // Store exactly what the editor sent (validated shape only); reads go
        // through StudioContent::merge(), so defaults fill any gap.
        $company->content = $request->validated('content');
        $company->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Home sections updated.')]);

        return back();
    }
}
