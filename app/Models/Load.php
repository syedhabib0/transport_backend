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
        'unit_no',
        'bill_id',
        'load_type',
        'weight',
        'destination',
        'pickup_location',
        'dropoff_location',
        'pickup_date',
        'delivery_date',
        'status',
        'total_fare',
        'driver_fare',
        'pickup_time',
        'dropoff_time',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function documents()
    {
        return $this->hasMany(LoadDocument::class);
    }

    public function pickUpLocation()
    {
        return $this->hasOne(PickUpLocation::class);
    }

    public function dropOffLocation()
    {
        return $this->hasOne(DropOffLocation::class);
    }

    public function currentLocation()
    {
        return $this->hasOne(LoadStatus::class);
    }
}
