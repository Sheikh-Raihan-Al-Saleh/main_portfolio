<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Edits the site-wide profile singleton. Named SiteProfileController to avoid
 * colliding with the starter kit's account-level Settings\ProfileController.
 */
class SiteProfileController extends Controller
{
    use HandlesMediaUploads;

    public function edit(): Response
    {
        return inertia('admin/Profile', ['profile' => Profile::current()]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $profile = Profile::current();

        $profile->fill($request->safe()->only([
            'name', 'hero_title', 'hero_statement', 'headline', 'tagline',
            'bio', 'location', 'public_email', 'phone', 'available_for_work',
            'roles', 'socials', 'footer', 'content', 'meta_title',
            'meta_description',
        ]));

        foreach ([
            ['avatar', 'avatar_path', 'remove_avatar', 'profile'],
            ['og_image', 'og_image_path', 'remove_og_image', 'profile'],
            ['resume', 'resume_path', 'remove_resume', 'profile/resume'],
        ] as [$input, $column, $removeFlag, $directory]) {
            if ($request->boolean($removeFlag)) {
                $this->deleteMedia($profile->{$column});
                $profile->{$column} = null;
            }

            $profile->{$column} = $this->storeMedia(
                $request->file($input), $directory, $profile->{$column},
            );
        }

        $profile->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return back();
    }
}
