<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'cow_id',
        'vaccine_name',
        'administered_at',
    ];

    protected $casts = [
        'administered_at' => 'date',
    ];

    public function cow()
    {
        return $this->belongsTo(Cow::class);
    }
}
