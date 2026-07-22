<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Response as SurveyResponse;
use App\Services\ReviewClickService;
use Illuminate\Http\RedirectResponse;

class ReviewClickController extends Controller
{
    public function __construct(private readonly ReviewClickService $reviewClicks)
    {
    }

    public function redirect(string $response, string $channel): RedirectResponse
    {
        $response = SurveyResponse::query()->where('uuid', $response)->firstOrFail();

        $target = $this->reviewClicks->logAndResolve($response, $channel);

        abort_unless($target, 404);

        return redirect()->away($target);
    }
}
