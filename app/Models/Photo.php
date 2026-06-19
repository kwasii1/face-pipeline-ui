<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Photo extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'batch_id',
        'path',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function photoBatch(): BelongsTo
    {
        return $this->belongsTo(PhotoBatch::class, 'batch_id');
    }

    public function faces(): HasMany
    {
        return $this->hasMany(Face::class);
    }
}
