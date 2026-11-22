<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\FounderService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The founder's resume (/resume), on a stable URL whatever the file is called.
 */
class ResumeController extends Controller
{
    public function __construct(
        private readonly FounderService $founders,
    ) {}

    public function __invoke(): StreamedResponse
    {
        $response = $this->founders->resumeResponse();

        abort_if($response === null, 404);

        return $response;
    }
}
