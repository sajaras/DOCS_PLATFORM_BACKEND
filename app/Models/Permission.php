<?php

namespace App\Models;

use App\Models\User; // 👈 1. Import the User model
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * A permission can be applied to users.
     * Overridden to directly use the App\Models\User class.
     */

    protected static function booted(): void
    {
        // This global scope will automatically filter roles by the current user's organization.
        static::addGlobalScope('organization', function (Builder $builder) {
            if (auth()->check() && auth()->user()->organization->count()) {
                $builder->where('organization_id', auth()->user()->organization->id);
            }
        });

        // This will automatically set the organization_id when a new role is created.
        static::creating(function ($role) {
            if (auth()->check() && auth()->user()->organization->count()) {
                $role->organization_id = auth()->user()->organization->id;
            }
        });
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            // 👇 2. Replace the dynamic helper with the static class reference
            User::class,
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.permission_pivot_key'),
            config('permission.column_names.model_morph_key')
        );
    }
}
