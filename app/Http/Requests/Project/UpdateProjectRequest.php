<?php

namespace App\Http\Requests\Project;

/**
 * Editing a project. Same rules as creating one; the slug rule already ignores this project.
 */
class UpdateProjectRequest extends StoreProjectRequest {}
