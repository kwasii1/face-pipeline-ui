<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory, HasUuids;

    protected static function booted(): void
    {
        static::deleting(function (Photo $photo) {
            $photo->loadMissing('faces');

            foreach ($photo->faces as $face) {
                Storage::disk('shared')->delete($face->crop_path);
            }

            Storage::disk('shared')->delete($photo->path);
        });
    }

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
