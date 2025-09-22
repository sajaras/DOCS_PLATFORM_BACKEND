<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// NOTE: This model does not need auditing as it is already a log of changes.
class Version extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'organization_id',
        'document_id',
        'author_id',
        'content',
        'change_summary',
    ];

    /**
     * Get the document that this version belongs to.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the author of this version.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}