<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoBatch extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'total_photos',
        'processed_photos',
        'status',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'batch_id');
    }
}
