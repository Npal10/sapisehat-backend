<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cow extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'ear_tag',
        'gender',
        'age',
        'breed',
        'weight',
        'last_vaccinated_at',
        'photo_url',
        'status',
        'acquisition_date',
        'acquisition_place',
    ];

    protected $casts = [
        'last_vaccinated_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }

    public function vaccines()
    {
        return $this->hasMany(VaccinationHistory::class)->orderBy('administered_at', 'desc');
    }
}
