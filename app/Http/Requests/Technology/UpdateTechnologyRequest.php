<?php

namespace App\Http\Requests\Technology;

/**
 * Editing a technology. Same rules as creating one; the unique-name check ignores this technology.
 */
class UpdateTechnologyRequest extends StoreTechnologyRequest {}
