<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $table = 'employee';

    protected $fillable = [
        'nik',
        'name',
        'department',
        'employment_status',
        'position',
    ];

    public function voting(): HasOne
    {
        return $this->hasOne(Voting::class);
    }
}
