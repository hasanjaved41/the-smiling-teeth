<?php

namespace App\Services\ServiceCalls;

use App\Exceptions\ServiceCallFailedException;
use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use Ixudra\Curl\Facades\Curl;
use Psy\Util\Json;

/**
 * Class AuthServiceCall
 * @package App\Services
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class AuthServiceCall
{
    /**
     * Curl call for generate token for login.
     *
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @since 1.0.0
     */
    public static function generateToken(array $data)
    {
        $authorization_api = config('endpoints.auth-service') . "api/v1/generate-token";

        $response = Curl::to($authorization_api)
            ->withHeader('X-Request-Id:' . AppLog::getRequestId())
            ->withData(array('key_id' => $data['client_id'], 'reference_number' => $data['reference_number'], 'reference_type' => $data['reference_type'], 'role_id' => $data['role_id']))
            ->returnResponseObject()
            ->asJson(true)
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
            throw new ServiceCallFailedException('Login Service generateToken did not respond with 200 : ' . $message);
        } else {
            return $response->content;
        }
    }
}
