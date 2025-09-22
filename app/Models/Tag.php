<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Tag extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'name',
        'slug',
    ];

    /**
     * Get the organization that owns the tag.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The documents that belong to the tag.
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'document_tag');
    }
}