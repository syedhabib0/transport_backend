<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'vehicle_type',
        'unit_number',
        'make',
        'model',
        'payload_weight',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function licenseDetails()
    {
        return $this->hasOne(VehicleLicenseDetail::class);
    }

    public function images()
    {
        return $this->hasOne(VehicleImage::class);
    }

    public function otherDetails()
    {
        return $this->hasOne(VehicleOtherDetail::class);
    }

    public function details()
    {
        return $this->hasOne(VehicleDetail::class);
    }

    // Search function
    public function scopeFilter($query, $filters)
    {
        if (isset($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        if (isset($filters['unit_number'])) {
            $query->where('unit_number', $filters['unit_number']);
        }

        if (isset($filters['make'])) {
            $query->where('make', $filters['make']);
        }

        if (isset($filters['model'])) {
            $query->where('model', $filters['model']);
        }
        
        if (isset($filters['payload_weight'])) {
            $query->where('payload_weight', $filters['payload_weight']);
        }
        // Add more filters as needed...

        return $query;
    }
}
