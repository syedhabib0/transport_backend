<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\SpatialBuilder;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;


/**
 * @property Point $location
 * @property Polygon $area
 * @method static SpatialBuilder query()
 */
class Driver extends Model
{
    use HasFactory, HasSpatial;

    protected $fillable = [
        'user_id',
        'profile_id',
        'hired_by',
        'status',
        'note',
        'location', // Spatial field for storing the driver's real-time location
        'area',
        'is_available', // Boolean to indicate whether the driver is available
    ];
    
    // Spatial field definition
    protected $spatialFields = [
        'location' => 'point',
        'area' => 'polygon',
    ];

    protected $casts = [
        'location' => Point::class,
        'area' => Polygon::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function hiredBy()
    {
        return $this->belongsTo(User::class, 'hired_by');
    }

    public function licenseDetails()
    {
        return $this->hasOne(DriverLicenseDetail::class);
    }

    public function insuranceDetails()
    {
        return $this->hasOne(DriverInsuranceDetail::class);
    }

    public function details()
    {
        return $this->hasOne(DriverDetail::class);
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function loads()
    {
        return $this->hasMany(Load::class);
    }

    // Broadcasting real-time location
    protected $dispatchesEvents = [
        'updated' => \App\Events\DriverLocationUpdated::class,
    ];
}

