<?php

namespace App\Services\Traits;

trait ResponseCodeTrait
{
    /**
     * to get data for responseCode
     * @author Karan Shah <karan.shah@kissht.com>
     * @param int $code Response code param
     * @return array
     */
    public function getResponseCode($code)
    {
        $responseCode = [
            /*
            |--------------------------------------------------------------------------
            | GENERAL SUCCESS RESPONSE CODE
            |--------------------------------------------------------------------------
            */
            '1' => ['request_id'=>'','success' => true, 'response_code' => 0, 'message' => 'Success','http_code'=>200],

            /*
            |--------------------------------------------------------------------------
            | GENERAL ERROR RESPONSE CODE
            |--------------------------------------------------------------------------
            */
            '101' => ['request_id'=>'','success' => false, 'response_code' => 101, 'message' => 'Valdition errors','http_code'=>400],
            '102' => ['request_id'=>'','success' => false, 'response_code' => 102, 'message' => 'Application errors','http_code'=>200],
            '103' => ['request_id'=>'','success' => false, 'response_code' => 103, 'message' => 'Request Id missing in header','http_code'=>400],
            '104' => ['request_id'=>'','success' => false, 'response_code' => 104, 'message' => 'Record not found','http_code'=>404],

            /*
            |--------------------------------------------------------------------------
            | SERVICE SPECIFIC RESPONSE CODE
            |--------------------------------------------------------------------------
            */
            '201' => ['request_id'=>'','success' => false, 'response_code' => 201, 'message' => '','http_code'=>200],
        ];

        return $responseCode[$code];
    }
}
