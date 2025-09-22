<?php

namespace App\Models;

use App\Models\User; // 👈 1. Import the User model
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // protected $appends = ['permission_ids'];
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

    public function getPermissionIdsAttribute()
    {
        // Eager load permissions to avoid N+1 query problem
        return $this->permissions->pluck('id');
    }
}
