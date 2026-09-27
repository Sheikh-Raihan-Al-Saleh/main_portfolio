<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\HandlesMediaUploads;
use App\Enums\LandingSectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Edits the company singleton shown on the home route, plus the section builder
 * for that route. The founder behind it is a separate record, edited by
 * SiteProfileController.
 */
class CompanyController extends Controller
{
    use HandlesMediaUploads;

    public function edit(): Response
    {
        $company = Company::current();

        return inertia('admin/Company', [
            'company' => $company,
            'sections' => $company->sections()->ordered()->get(),
            'sectionTypes' => LandingSectionType::options(),
        ]);
    }

    public function update(CompanyRequest $request): RedirectResponse
    {
        $company = Company::current();

        $company->fill($request->safe()->only([
            'name', 'legal_name', 'headline', 'tagline', 'bio', 'mission',
            'location', 'public_email', 'phone', 'website', 'founded_year',
            'hero_eyebrow', 'hero_title', 'hero_statement', 'primary_cta_label',
            'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
            'status_text', 'accepting_projects', 'socials', 'footer',
            'meta_title', 'meta_description',
        ]));

        foreach ([
            ['logo', 'logo_path', 'remove_logo'],
            ['og_image', 'og_image_path', 'remove_og_image'],
        ] as [$input, $column, $removeFlag]) {
            if ($request->boolean($removeFlag)) {
                $this->deleteMedia($company->{$column});
                $company->{$column} = null;
            }

            $company->{$column} = $this->storeMedia(
                $request->file($input), 'company', $company->{$column},
            );
        }

        $company->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Company updated.')]);

        return back();
    }
}
