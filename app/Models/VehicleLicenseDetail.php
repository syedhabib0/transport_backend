<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleLicenseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'license_plate_image',
        'license_plate_state',
        'license_plate_expiry',
        'is_expirable',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
