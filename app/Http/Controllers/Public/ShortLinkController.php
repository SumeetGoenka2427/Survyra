<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ShortLinkService;
use Illuminate\Http\RedirectResponse;

class ShortLinkController extends Controller
{
    public function __construct(private readonly ShortLinkService $shortLinks)
    {
    }

    public function redirect(string $code): RedirectResponse
    {
        $result = $this->shortLinks->resolveAndTrack($code);

        abort_if(! $result, 404);

        return redirect()->away($result['url']);
    }
}
