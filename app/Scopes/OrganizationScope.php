<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrganizationScope implements Scope
{
    protected $relationPath;
    public function __construct($relationPath)
    {
        $this->relationPath = $relationPath;
    }


    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {

        $organizations = Auth::user()->organizations;


        if (count($organizations)) {
            if ($this->relationPath == null) {
                $builder->where('organization_id',  $organizations->first()->id);
            }
            else
            {
                $organizationId =  $organizations->first()->id;
                $builder->whereHas($this->relationPath, function ($query) use ($organizationId) {
                    $query->where('organization_id', $organizationId);
                });
            }
        }
    }
}
