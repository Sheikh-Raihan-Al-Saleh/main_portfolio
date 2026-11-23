<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * Saves a drag-and-drop order: each ID gets its position as `sort_order`.
 */
class SortOrder
{
    /**
     * Only rows inside $query are updated, so an ID from another list (or a
     * forged one) is simply ignored.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>|Relation<TModel, *, *>  $query
     * @param  array<int, int>  $orderedIds  IDs in their new order, first to last.
     */
    public static function apply(Builder|Relation $query, array $orderedIds): void
    {
        DB::transaction(function () use ($query, $orderedIds): void {
            foreach (array_values($orderedIds) as $position => $id) {
                (clone $query)->whereKey($id)->update(['sort_order' => $position]);
            }
        });
    }
}
