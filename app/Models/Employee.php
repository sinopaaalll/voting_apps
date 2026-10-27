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
        'birth_date',
        'department',
        'employment_status',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function voting(): HasOne
    {
        return $this->hasOne(Voting::class);
    }
}
