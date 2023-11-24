<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'license_id',
        'insurance_id',
        'is_truck_allotted',
        'vehicle_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function license()
    {
        return $this->belongsTo(DriverLicenseDetail::class, 'license_id');
    }

    public function insurance()
    {
        return $this->belongsTo(DriverInsuranceDetail::class, 'insurance_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
