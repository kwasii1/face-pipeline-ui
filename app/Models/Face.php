<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Face extends Model
{
    use HasFactory, HasUuids;

    protected static function booted(): void
    {
        static::deleting(function (Face $face) {
            Storage::disk('shared')->delete($face->crop_path);
        });
    }

    protected $fillable = [
        'photo_id',
        'person_id',
        'cluster_id',
        'bbox',
        'crop_path',
        'det_score',
        'blur_score',
    ];

    protected $casts = [
        'bbox' => 'array',
    ];

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
