<?php

namespace App\Services\ServiceCalls;

use App\Exceptions\ServiceCallFailedException;
use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use App\Helpers\UtilHelper;
use Ixudra\Curl\Facades\Curl;

class CommunicationServiceCall
{
    /**
     * send Otp
     * Curl call for send sms with otp on mobile number
     * @param json data
     * @return string
     * @throws ServiceCallFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public static function sendOtp($data)
    {
        $url = config('endpoints.communication-service').'/api/sendSms';
        $response = Curl::to($url)
            ->withHeader('X-Request-Id:' . AppLog::getRequestId())
            ->withData($data)
            ->returnResponseObject()
            ->asJsonResponse(true)
            ->post();

        if ($response->status == 400) {
            $message = '';
            if (isset($response->content['message'])) {
                $message = $response->content['message'];
            }
            throw new ValidationFailedException($message);
        } elseif ($response->status < 200 || $response->status > 206) {
            $message = '';
            if (isset($response->content, $response->content['message'])) {
                $message = $response->content['message'];
            }
            throw new ServiceCallFailedException('CommunicationService sendOtp did not respond with 200 : ' . $message);
        } else {
            return $response->content;
        }
    }

    /**
     * subuser sync
     * Curl call for subuser sync
     * @param json data
     * @return string
     * @throws ServiceCallFailedException
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public static function subuserSync(string $subuser_reference_number)
    {
        $url = config('endpoints.communication-cron-service') . 'api/v1/employees/'.$subuser_reference_number.'/publish-employee-event?event_name=user_sync';

        $request_id = AppLog::getRequestId();
        if(empty($request_id)) {
            $request_id = UtilHelper::generateString(true);
            AppLog::setRequestId($request_id);
        }

        $response = Curl::to($url)
            ->withHeader('x-request-id: ' . AppLog::getRequestId())
            ->asJson(true)
            ->returnResponseObject()
            ->get();

        if ($response->status < 200 || $response->status > 206) {
            $message = '';
            if (isset($response->content['message'])) {
                $message = $response->content['message'];
            }
            throw new ServiceCallFailedException('CommunicationServiceCall subuserSync non2xx : ' . $message);
        } else {
            return $response->content;
        }
    }
}
