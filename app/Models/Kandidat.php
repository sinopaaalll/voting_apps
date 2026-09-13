<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kandidat extends Model
{
    protected $table = 'kandidat';

    protected $fillable = [
        'nomor_urut',
        'name',
        'photo',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Voting::class);
    }
}
