<?php

use Carbon\Carbon;

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
}