<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthorizeStoreAccess
{
    public function handle(Request $request, Closure $next)
    {
        $storeId = $request->route('store') ? $request->route('store')->id : $request->input('store_id');

        $hasAccess = $request->user()->stores()->where('store_id', $storeId)
            ->where(function ($query) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->exists(); // Use exists() to check if there's at least one matching record

        if (!$hasAccess) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}