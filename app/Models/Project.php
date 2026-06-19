<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'description',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }

    public function photoBatches(): HasMany
    {
        return $this->hasMany(PhotoBatch::class);
    }

    public function faces(): HasManyThrough
    {
        return $this->hasManyThrough(Face::class, Photo::class);
    }
}
