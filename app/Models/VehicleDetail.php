<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'license_details_id',
        'images_id',
        'other_details_id',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function licenseDetails()
    {
        return $this->belongsTo(VehicleLicenseDetail::class, 'license_details_id');
    }

    public function images()
    {
        return $this->belongsTo(VehicleImage::class, 'images_id');
    }

    public function otherDetails()
    {
        return $this->belongsTo(VehicleOtherDetail::class, 'other_details_id');
    }
}
