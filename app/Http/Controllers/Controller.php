<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

/**
 * Base controller: shared helpers for every controller.
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
}
