<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Organization extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'logo_path',
    ];

    /**
     * Get the users associated with the organization.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the documents associated with the organization.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the tags associated with the organization.
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}