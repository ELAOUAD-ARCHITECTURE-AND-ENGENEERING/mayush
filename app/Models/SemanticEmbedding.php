<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SemanticEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'embeddable_id',
        'embeddable_type',
        'vector',
        'content_hash',
        'content',
        'metadata'
    ];

    public function embeddable()
    {
        return $this->morphTo();
    }
}
