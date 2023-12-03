<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Load extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'load_type',
        'weight',
        'destination',
        'pickup_location',
        'dropoff_location',
        'pickup_date',
        'delivery_date',
        'status',
        'driver_earnings',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function documents()
    {
        return $this->hasMany(LoadDocument::class);
    }
}
