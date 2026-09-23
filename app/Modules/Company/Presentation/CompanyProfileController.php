<?php

namespace App\Modules\Company\Presentation;

use App\Modules\Company\Application\GetCompanyProfile;
use App\Modules\MarketData\Application\Exception\MarketDataUnavailable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyProfileController
{
    public function __invoke(Request $request, string $symbol, GetCompanyProfile $profile)
    {
        $company = null;
        $error = null;
        $status = 200;
        try {
            $company = $profile->execute((string) $request->user()->id, $symbol);
        } catch (MarketDataUnavailable $exception) {
            $error = $exception->details();
            $status = $exception->status;
        }
        $returnTo = $request->query('from', '/temukan-saham');
        if (! is_string($returnTo) || ! preg_match('~\A/temukan-saham(?:\?[^\r\n]*)?\z~', $returnTo)) {
            $returnTo = '/temukan-saham';
        }

        return Inertia::render('nusalens/company-profile', compact('company', 'error', 'returnTo'))
            ->toResponse($request)->setStatusCode($status);
    }
}
