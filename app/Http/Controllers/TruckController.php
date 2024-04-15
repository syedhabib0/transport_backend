<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TruckController extends Controller
{
    public function getTruckTypes()
    {
        return successResponse('Truck types fetched successfully', getTruckTypes());
    }

    public function getTruckTypesForFindTruck()
    {
        return successResponse('Truck types fetched successfully', getTruckTypesForFindTruck());
    }
}
