<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOtherDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'length',
        'height',
        'width',
        'dimension_in',
        'is_available',
        'lift_gate',
        'hazmat',
        'icc_bar',
        'tsa',
        'twic',
        'pallet_jack',
        'true_dock_high',
        'tanker_endorsement',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
