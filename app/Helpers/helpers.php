<?php

use Carbon\Carbon;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;

if (!function_exists('getCurrentUser')) {
    function getCurrentUser()
    {
        return auth()->user();
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse($error, $code = 400, $data = [])
    {
        $response = [
            'success' => false,
            'status' => $code,
            'message' => $error,
            'data' => []
        ];

        return response()->json($response, $code);
    }
}

if (!function_exists('successResponse')) {
    function successResponse($message, $result = [], $code = 200, $paginate = false)
    {
        $resultData = [];
        $resultData = $result;
        if ($paginate) {
            $resultData = paginate($result);
        }

        $response = [
            'success' => true,
            'status' => 200,
            'message' => $message,
            'data' => $resultData
        ];
        return response()->json($response, 200);
    }
}

if (!function_exists('paginate')) {
    function paginate($data)
    {

        $paginationArray = null;
        if ($data) {
            $paginationArray = array('data' => $data->items(), 'pagination' => []);
            $paginationArray['pagination']['total'] = $data->total();
            $paginationArray['pagination']['current'] = $data->currentPage();
            $paginationArray['pagination']['first'] = 1;
            $paginationArray['pagination']['last'] = $data->lastPage();

            if ($data->hasMorePages()) {
                if ($data->currentPage() == 1) {
                    $paginationArray['pagination']['previous'] = 0;
                } else {
                    $paginationArray['pagination']['previous'] = $data->currentPage() - 1;
                }
                $paginationArray['pagination']['next'] = $data->currentPage() + 1;
            } else {
                $paginationArray['pagination']['previous'] = $data->currentPage() - 1;
                $paginationArray['pagination']['next'] = $data->lastPage();
            }
            if ($data->lastPage() > 1) {
                $paginationArray['pagination']['pages'] = range(1, $data->lastPage());
            } else {
                $paginationArray['pagination']['pages'] = [1];
            }
            $paginationArray['pagination']['from'] = $data->firstItem();
            $paginationArray['pagination']['to'] = $data->lastItem();

            return $paginationArray;
        }
    }
}

if (!function_exists('getTruckTypes')) {
    function getTruckTypes()
    {
        return [
            'sprinter-vans' => 'Sprinter Vans',
            'box-trucks' => 'Box Trucks',
            'reefers' => 'Reefers',
            'hazmat' => 'Hazmat',
            'straight-trucks' => 'Straight Trucks',
            'dry-van' => 'Dry Van',
            'flatbed' => 'Flatbed',
            'conestoga' => 'Conestoga'
        ];
    }
}

if (!function_exists('getTruckTypesForFindTruck')) {
    function getTruckTypesForFindTruck()
    {
        return [
            'all' => 'All Type',
            'sprinter-vans' => 'Sprinter Vans',
            'box-trucks' => 'Box Trucks',
            'reefers' => 'Reefers',
            'hazmat' => 'Hazmat',
            'straight-trucks' => 'Straight Trucks',
            'dry-van' => 'Dry Van',
            'flatbed' => 'Flatbed',
            'conestoga' => 'Conestoga'
        ];
    }
}

if (!function_exists('getDriverStatus')) {
    function getDriverStatus()
    {
        return [
            "available" => "Available",
            "not-available" => "Not Available",
            "will-be-available" => "Will Be Available",
            "under-our-load" => "Under Our Load",
            "under-our-bid" => "Under Our Bid",
            "suspended" => "Suspended"
        ];
    }
}

if (!function_exists('getLoadStatus')) {
    function getLoadStatus()
    {
        return [
            "available" => "Available",
            "active" => "Active",
            "on-going" => "On Going",
            "cancelled" => "Cancelled",
            "delivered" => "Delivered"
        ];
    }
}

if (!function_exists('sendPushNotification')) {
    function sendPushNotification($title, $body, $fcmToken)
    {
        $message = CloudMessage::fromArray([
            'token' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
        ]);
        Firebase::messaging()->send($message);
    }
}
