<?php

namespace App\Traits;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait HelperTrait
{

    function getOrganizationId($request)
    {
        $organizationId = null;
        if (Auth::check() && Auth::user()->currentOrganization) {
            $organizationId = Auth::user()->currentOrganization->id;
        }

        if (Auth::check() && Auth::user()->is_ris_admin && $request->has('organization_id')) {
            $requestedOrgId = $request->input('organization_id');
            if (Organization::find($requestedOrgId)) {
                $organizationId = $requestedOrgId;
            } else {
                throw \Illuminate\Validation\ValidationException::withMessages(['organization_id' => 'The specified organization does not exist.']);
            }
        } elseif (Auth::check() && Auth::user()->is_ris_admin && !$request->organization_id ) {
            throw \Illuminate\Validation\ValidationException::withMessages(['organization_id' => 'Organization ID is required for RIS Admin.']);
        }

        return $organizationId;
    }
}
