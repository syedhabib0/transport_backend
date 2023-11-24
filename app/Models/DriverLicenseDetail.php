<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverLicenseDetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'driver_id',
        'license_number',
        'license_expiry_date',
        'is_expirable',
        'license_issuance_country',
        'license_issuance_state',
        'license_photo_front',
        'license_photo_back',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
