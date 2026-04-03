<?php

namespace App\Services\ServiceCalls;

use App\Exceptions\ServiceCallFailedException;
use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use Ixudra\Curl\Facades\Curl;

class CollectionsServiceCall
{
    /**
     * @param $subuser_reference_number
     * @return mixed
     * @throws ServiceCallFailedException
     * @throws ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public static function getCollectionsDetails($subuser_reference_number)
    {
        AppLog::debug('CollectionsServiceCall', 'getCollectionsDetails', 'Collection Details Request', ['subuser_reference_number' => $subuser_reference_number]);

        $url = config('endpoints.collections-service') . 'api/v1/subusers/collections/' . $subuser_reference_number;

        $response = Curl::to($url)
            ->withHeader('Cache-Control: no-cache')
            ->withHeader('x-request-id: ' . AppLog::getRequestId())
            ->returnResponseObject()
            ->asJson(true)
            ->get();

        AppLog::debug('CollectionsServiceCall', 'getCollectionsDetails', 'Collection Details Response', [$response]);

        if ($response->status < 200 || $response->status > 206) {
            $message = '';
            if (isset($response->content['message'])) {
                $message = $response->content['message'];
            }
            if ($response->status == 400) {
                throw new ValidationFailedException($message);
            } else {
                throw new ServiceCallFailedException('CollectionServiceCall getCollectionsDetails did not respond with 200 : ' . $message);
            }
        } else {
            return $response->content;
        }
    }
}
