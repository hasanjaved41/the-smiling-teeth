<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use AppLog;
use Util;
use App\Services\Traits\ResponseCodeTrait;
use Validator;
use App\Http\Resources\BasicAuth as BasicAuthResource;

/**
 * Class AuthController
 * @package Controllers
 * @author Mukesh Kurmi<mukesh.kurmi@kissht.com>
 * @since 1.0.0
 */
class AuthController extends Controller
{
    use ResponseCodeTrait;

    private $authService;

    function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Verify Key
     * @param array $request
     * @return string
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     */
    public function verifyKeys(Request $request)
    {
        $required_fields = [
            'key_id',
            'key_secret'
        ];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);
        $result = $this->authService->verifyKeys($request->all());
        if (!empty($result)) {
            $response = $this->getResponseCode(1);
            $response['data'] = ['auth' => new BasicAuthResource($result)];
        } else {
            $response = $this->getResponseCode(201);
        }
        return $this->response($response);
    }

    /**
     * Generate Token
     * @param array $request
     * @return string
     * @throws AuthServiceException
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     */
    public function generateToken(Request $request)
    {
        $required_fields = [
            'key_id',
            'reference_number',
            'reference_type'
        ];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);
        $token = $this->authService->generateToken($request->all());
        $response = $this->getResponseCode(1);
        $response['data']['token'] = (string) $token;
        return $this->response($response);
    }

    /**
     * Verify Token
     * @param array $request
     * @return string
     * @throws AuthServiceException
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     */
    public function verifyToken(Request $request)
    {
        $required_fields = [
            'token'
        ];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);
        $token = \JWTAuth::getToken();
        $result = $this->authService->verifyToken($token);
        $response = $this->getResponseCode(1);
        $response['data'] = $result;
        return $this->response($response);
    }


    /**
     * Verify key Id
     * @param array $request
     * @return string
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     */
    public function verifyKeyId(Request $request)
    {
        $required_fields = [
            'key_id'
        ];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);
        $result = $this->authService->verifyKeyId($request->all());
        $response = $this->getResponseCode(1);
        $response['data']['is_verified'] = $result;
        return $this->response($response);
    }

    /**
     * Refresh Token
     * @param array $request
     * @return string
     * @throws AuthServiceException
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     */
    public function refreshToken(Request $request)
    {
        $required_fields = [
            'token'
        ];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);
        $token = \JWTAuth::getToken();
        $new_token = $this->authService->refreshToken($token);
        $response = $this->getResponseCode(1);
        $response['data']['token'] = (string) $new_token;
        return $this->response($response);
    }

    /**
     * Encrypt String
     * @param array $request
     * @return string
     * @throws AuthServiceException
     * @author Gopal Panadi <gopal.panadi@kissht.com>
     * @since 1.0.0
     */
    public function encrypt(Request $request)
    {
        $data = $request->all();

        $required_fields = ['value'];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);

        $encrypted_string = $this->authService->encryptString($data['value']);
        $response = $this->getResponseCode(1);
        $response['data']['encrypted_string'] = (string) $encrypted_string;
        return $this->response($response);
    }

    /**
     * Decrypt String
     * @param array $request
     * @return string
     * @throws AuthServiceException
     * @author Gopal Panadi <gopal.panadi@kissht.com>
     * @since 1.0.0
     */
    public function decrypt(Request $request)
    {
        $data = $request->all();

        $required_fields = ['value'];
        $rules = $this->authService->getRules($required_fields);
        $this->validate($request->all(), $rules);

        $encrypted_string = $this->authService->decryptString($data['value']);
        $response = $this->getResponseCode(1);
        $response['data']['decrypted_string'] = (string) $encrypted_string;
        return $this->response($response);
    }
}
