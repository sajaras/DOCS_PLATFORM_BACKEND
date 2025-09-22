<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Document extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'parent_id',
        'title',
        'slug',
        'content',
        'author_id',
        'status',
        'order',
    ];

    /**
     * Get the organization that owns the document.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who authored the document.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the parent document.
     */
    public function parent()
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    /**
     * Get all of the child documents.
     */
    public function children()
    {
        return $this->hasMany(Document::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get all versions of the document.
     */
    public function versions()
    {
        return $this->hasMany(Version::class)->latest();
    }

    /**
     * The tags that belong to the document.
     */
       public function tags()
    {
       
        return $this->belongsToMany(Tag::class, 'document_tags');
    }
}