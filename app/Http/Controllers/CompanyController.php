<?php

namespace App\Http\Controllers;

use App\Actions\PresentsLandingSection;
use App\Models\Company;
use App\Models\LandingSection;
use App\Models\Profile;
use App\Models\Project;
use Inertia\Response;

/**
 * The company site. It owns the public home route, the About page and the
 * project archive; the founder's own portfolio lives on /founder, rendered by
 * PortfolioController.
 */
class CompanyController extends Controller
{
    /**
     * The company home page: a hero, the company's published work, and the
     * section blocks composed in the admin.
     */
    public function index(): Response
    {
        $company = Company::current();

        $present = app(PresentsLandingSection::class);

        $sections = $company->sections()
            ->visible()
            ->ordered()
            ->get()
            ->map(fn (LandingSection $section): array => $present($section));

        $totalProjects = Project::query()->published()->companyWork()->count();

        // The home page is an editorial selection; the archive shows the rest.
        $projects = Project::query()->published()->companyWork()->ordered()->take(6)->get();

        /**
         * A short preview of the founder's own work, so a visitor to the studio
         * site can still see what he builds himself before following the card to
         * his personal portfolio. The full set lives on /founder.
         */
        $personalProjects = Project::query()->published()->personalWork()->ordered()->take(3)->get();

        $clients = $company->clients()->visible()->ordered()->get();

        return inertia('public/Home', [
            'company' => $company,
            'sections' => $sections,
            'projects' => $projects,
            'totalProjects' => $totalProjects,
            'personalProjects' => $personalProjects,
            'totalPersonalProjects' => Project::query()->published()->personalWork()->count(),
            'clients' => $clients,
            'stats' => $this->publicStats($company, $clients->count()),
        ]);
    }

    /**
     * The company About page: who the studio is, how it works, and a card for
     * the founder that links out to his own portfolio.
     *
     * The home page's composed blocks are deliberately not repeated here — this
     * page has its own chapters — and the founder's personal work lives on
     * /founder, which the card sends the reader to.
     */
    public function about(): Response
    {
        $company = Company::current();

        $clients = $company->clients()->visible()->ordered()->get();

        return inertia('public/About', [
            'company' => $company,
            'profile' => Profile::current(),
            'clients' => $clients,
            'stats' => $this->publicStats($company, $clients->count()),
        ]);
    }

    /**
     * The claims the public site is allowed to make.
     *
     * A studio with no published work or no visible clients has nothing to
     * boast about, so those figures are reported as null and the sections drop
     * them instead of printing "0 clients served" to the world.
     *
     * @return array{projects: int|null, clients: int|null, foundedYear: int|null, yearsInBusiness: int|null}
     */
    private function publicStats(Company $company, int $visibleClientCount): array
    {
        $projects = Project::query()->published()->companyWork()->count();

        return [
            'projects' => $projects > 0 ? $projects : null,
            'clients' => $visibleClientCount > 0 ? $visibleClientCount : null,
            'foundedYear' => $this->foundedYear($company),
            'yearsInBusiness' => $this->yearsInBusiness($company),
        ];
    }

    /**
     * The founding year as a number, or null when it is unset or not a year.
     */
    private function foundedYear(Company $company): ?int
    {
        return is_numeric($company->founded_year)
            ? (int) $company->founded_year
            : null;
    }

    /**
     * Whole years between the founding year and today. Null before there is a
     * year to measure from, so the hero can drop the stat rather than show 0.
     */
    private function yearsInBusiness(Company $company): ?int
    {
        $founded = $this->foundedYear($company);

        if ($founded === null || $founded > (int) now()->year) {
            return null;
        }

        return now()->year - $founded;
    }
}
