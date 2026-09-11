<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voting extends Model
{
    protected $table = 'voting';

    protected $fillable = [
        'employee_id',
        'kandidat_id',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function kandidat(): BelongsTo
    {
        return $this->belongsTo(Kandidat::class);
    }
}
