<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Base controller with the two helpers many admin pages share: toast
 * notifications and saving a drag-and-drop order.
 */
abstract class Controller
{
    /**
     * Flash a toast notification for the next page the user sees.
     */
    protected function toast(string $message, string $type = 'success'): void
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);
    }

    /**
     * Save a drag-and-drop order: each ID gets its position as `sort_order`.
     * Only rows inside $query are updated, so an ID from another list (or a
     * forged one) is ignored.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>|Relation<TModel, *, *>  $query
     * @param  array<int, mixed>  $orderedIds  IDs in their new order, first to last.
     */
    protected function saveSortOrder(Builder|Relation $query, array $orderedIds): void
    {
        DB::transaction(function () use ($query, $orderedIds): void {
            foreach (array_values($orderedIds) as $position => $id) {
                (clone $query)->whereKey((int) $id)->update(['sort_order' => $position]);
            }
        });
    }
}
