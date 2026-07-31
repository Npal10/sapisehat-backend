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
        'confidence_score',
        'explanation',
        'recommendation',
    ];

    public function cow()
    {
        return $this->belongsTo(Cow::class);
    }
}
