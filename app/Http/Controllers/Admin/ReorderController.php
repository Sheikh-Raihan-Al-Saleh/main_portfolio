<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderRequest;
use App\Services\ReorderService;
use Illuminate\Http\RedirectResponse;

/**
 * Saves drag-and-drop order for any sortable admin list.
 * The {type} segment is constrained to ReorderService::TYPES by the route.
 */
class ReorderController extends Controller
{
    public function __construct(
        private readonly ReorderService $reorder,
    ) {}

    public function __invoke(ReorderRequest $request, string $type): RedirectResponse
    {
        $this->reorder->reorder($type, $request->validated('ids'));

        return back();
    }
}
