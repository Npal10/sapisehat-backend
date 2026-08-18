<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    protected $fillable = [
        'cow_id',
        'questionnaire_data',
        'description',
        'fmd_risk',
        'lsd_risk',
        'pmk_percentage',
        'lsd_percentage',
        'confidence_score',
        'explanation',
        'recommendation',
    ];

    protected $casts = [
        'pmk_percentage'   => 'float',
        'lsd_percentage'   => 'float',
        'confidence_score' => 'float',
    ];


    public function cow()
    {
        return $this->belongsTo(Cow::class);
    }
}
