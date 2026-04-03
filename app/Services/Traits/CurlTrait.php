<?php

namespace App\Services\Traits;

trait CurlTrait{

    /**
     * to request curl
     * @author Karan Shah <karan.shah@kissht.com>
     * @param string $url Url for curl request
     * @param string $type HTTP Method
     * @param array $headers Headers to be send
     * @param string|array Data to send json,xml,array
     * @param array $basicAuthHeaders basic auth username,password keys to send
     * @param string $bearerToken Bearer token for auth
     * @param array $params Other curl configuration keys to override
     * @param bool $encryptionMethod To encrypt request data
     * @return array
     */
    public function requestCurl($url, $type, $headers=[], $input='', $basicAuthHeaders=[], $bearerToken='', $params=[], $encryptionMethod = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($type));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        /* create headers array */
        $finalHeaders = $headers;
        if( !empty($basicAuthHeaders) ){
            $basicAuthString = base64_encode($basicAuthHeaders['username'] .':' .$basicAuthHeaders['password']);
            $finalHeaders[] = 'Authorization: Basic ' .$basicAuthString;
        } elseif( !empty($bearerToken) ){
            $finalHeaders[] = 'Authorization: Bearer ' .$bearerToken;
        }

        if ("POST" == strtoupper($type) || "DELETE" == strtoupper($type) || "PUT" == strtoupper($type) || "PATCH" == strtoupper($type)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
            if (true == $encryptionMethod) {
                array_push($finalHeaders, 'Accept-Encoding: gzip, deflate');
            }
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $finalHeaders);

        $response = curl_exec($ch);
        $information = curl_getinfo($ch);
        curl_close($ch);

        return ['response'=>$response,'http_code'=>$information['http_code']];
    }

}
