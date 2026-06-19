<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonCentroid extends Model
{
    protected $primaryKey = 'person_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'person_id',
        'centroid',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
