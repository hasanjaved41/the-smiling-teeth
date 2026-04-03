<?php

namespace App\Services;

use App\Repositories\AuthKeyRepository;
use App\Services\Traits\ResponseCodeTrait;
use AppLog;
use App\Exceptions\AuthServiceException;
use Illuminate\Support\Facades\Crypt;

/**
 * Class AuthService
 * @author Mukesh Kurmi<mukesh.kurmi@kissht.com>
 * @since 1.0.0
 */
class AuthService
{
    use ResponseCodeTrait;

    private $authKeyRepo;

    function __construct(AuthKeyRepository $authRepo)
    {
        $this->authKeyRepo = $authRepo;
    }

    /**
     * To verify keys
     * @param array $credentials
     * @return string
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     *
     */
    function verifyKeys($credentials)
    {
        $key_id = $credentials['key_id'];
        $response = [];
        $client_id_details = $this->getClientIdDetails($key_id);
        if (!empty($client_id_details)) {
            $secret_key = $client_id_details['key_secret'];
            if ($secret_key == $credentials['key_secret']) {
                $response = $client_id_details;
            }
        }
        return $response;
    }


    /**
     * To create token for authorization
     * @param array $param
     * @return string
     * @throws AuthServiceException
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     *
     */
    public function generateToken($param)
    {
        $custom_claims = [
            'key_id' => $param['key_id'],
            'client_id' => $param['key_id'],
            'reference_number' => $param['reference_number'],
            'reference_type' => $param['reference_type'],
            'iss' => 'Auth Service'
        ];
        if (!empty($param['role_id'])) {
            $custom_claims['role_id'] = $param['role_id'];
        }
        $client_id_details = $this->getClientIdDetails($param['key_id']);
        if (!empty($client_id_details)) {
            $expiration_minutes = $client_id_details['auth_client']['expiration_minutes'];
            $secret_key = $client_id_details['key_secret'];
            \JWTAuth::getJWTProvider()->setSecret($secret_key);
            \JWTAuth::factory()->setTTL($expiration_minutes);
            $payload = \JWTFactory::claims($custom_claims)->sub($param['reference_number'])->aud($param['reference_type'])->make();
            $token = \JWTAuth::encode($payload);
            $response = $token;
        } else {
            throw new AuthServiceException('Invalid Key Id');
        }
        return $response;
    }

    /**
     * Get client details by key_id
     * @param string $key_id
     * @return string
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     */
    private function getClientIdDetails($key_id)
    {
        return $this->authKeyRepo->getClientIdDetails($key_id);
    }

    /**
     * Verify Token
     * @param string $token
     * @return array
     * @throws AuthServiceException
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     */
    public function verifyToken($token)
    {
        $token_explode = explode('.', $token);
        if (count($token_explode) == 3) {
            $payload = $token_explode[1];
            $client_details = json_decode(base64_decode($payload), true);
            if (!isset($client_details['key_id']) || !isset($client_details['reference_number']) || !isset($client_details['reference_type'])) {
                AppLog::critical(
                    'AuthService',
                    'verifyToken',
                    'missing_payload_element',
                    ['Key_id OR reference_number OR reference_type missing']
                );
                throw new AuthServiceException('Mandatory keys are missing');
            } else {
                $key_id = $client_details['key_id'];
                $reference_number = $client_details['reference_number'];
                $reference_type = $client_details['reference_type'];
                if (empty($key_id) || empty($reference_number) || empty($reference_type)) {
                    AppLog::critical(
                        'AuthService',
                        'verifyToken',
                        'empty_payload_element',
                        ['Key_id OR reference_number OR reference_type Empty']
                    );
                    throw new AuthServiceException('Mandatory keys are empty');
                } else {
                    $client_id_details = $this->getClientIdDetails($key_id);
                    if (!empty($client_id_details)) {
                        $secret_key = $secret_key = $client_id_details['key_secret'];;
                        try {
                            \JWTAuth::getJWTProvider()->setSecret($secret_key);
                            \JWTAuth::decode($token);
                            $response = [
                                'key_id' => $key_id,
                                'reference_number' => $reference_number,
                                'reference_type' => $reference_type
                            ];
                            if (!empty($client_details['role_id'])) {
                                $response['role_id'] = $client_details['role_id'];
                            }
                        } catch (\Exception $e) {
                            AppLog::critical('AuthService', 'verifyToken', 'exception_throw', [$e->getMessage()]);
                            throw new AuthServiceException($e->getMessage());
                        }
                    } else {
                        AppLog::critical('AuthService', 'verifyToken', 'invalid_key_id', ['Invalid Key_id in payload']);
                        throw new AuthServiceException('Invalid Key Id');
                    }
                }
            }
        } else {
            AppLog::critical(
                'AuthService',
                'verifyToken',
                'invalid_format',
                ['invalid token format']
            );
            throw new AuthServiceException('Invalid Token Format');
        }
        return $response;
    }

    /**
     * Verify key id
     * @param $params
     * @return string
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     */
    public function verifyKeyId($params)
    {
        $key_id = $params['key_id'];
        $client_id_details = $this->getClientIdDetails($key_id);
        if (!empty($client_id_details)) {
            $response = true;
        } else {
            $response = false;
        }
        return $response;
    }

    /**
     * Refresh Token
     * @param string $token
     * @return string
     * @throws AuthServiceException
     * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
     * @since 1.0.0
     */
    public function refreshToken($token)
    {
        $token_explode = explode('.', $token);
        if (count($token_explode) == 3) {
            $payload = $token_explode[1];
            $client_details = json_decode(base64_decode($payload), true);
            if (!isset($client_details['key_id']) || !isset($client_details['reference_number']) || !isset($client_details['reference_type'])) {
                AppLog::critical(
                    'AuthService',
                    'refreshToken',
                    'missing_payload_element',
                    ['Key_id OR reference_number OR reference_type missing']
                );
                throw new AuthServiceException('Mandatory keys are missing');
            } else {
                $key_id = $client_details['key_id'];
                $reference_number = $client_details['reference_number'];
                $reference_type = $client_details['reference_type'];
                if (empty($key_id) || empty($reference_number) || empty($reference_type)) {
                    AppLog::critical(
                        'AuthService',
                        'refreshToken',
                        'empty_payload_element',
                        ['Key_id OR reference_number OR reference_type Empty']
                    );
                    throw new AuthServiceException('Mandatory keys are empty');
                } else {
                    $client_id_details = $this->getClientIdDetails($key_id);
                    if (!empty($client_id_details)) {
                        $secret_key = $secret_key = $client_id_details['key_secret'];;
                        try {
                            \JWTAuth::getJWTProvider()->setSecret($secret_key);
                            \JWTAuth::decode($token);
                            $token = $this->generateToken([
                                'key_id' => $key_id,
                                'reference_number' => $reference_number,
                                'reference_type' => $reference_type
                            ]);
                            $response = $token;
                        } catch (\Exception $e) {
                            AppLog::critical('AuthService', 'refreshToken', 'exception_throw', [$e->getMessage()]);
                            throw new AuthServiceException($e->getMessage());
                        }
                    } else {
                        AppLog::critical('AuthService', 'refreshToken', 'invalid_key_id', ['Invalid Key_id in payload']);
                        throw new AuthServiceException('Invalid Key Id');
                    }
                }
            }
        } else {
            AppLog::critical(
                'AuthService',
                'refreshToken',
                'invalid_format',
                ['invalid token format']
            );
            throw new AuthServiceException('Invalid Token Format');
        }
        return $response;
    }

    /**
     * validation rules
     * @param array
     * @return array
     * @author Mukesh Kurmi
     */
    public function getRules(array $required_fields = [])
    {
        $rules = [];

        foreach ($required_fields as $field) {
            if (isset($rules[$field])) {
                $rules[$field] = 'required|' . $rules[$field];
            } else {
                $rules[$field] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Encrypt string
     * @param string
     * @return string
     * @author Gopal Panadi
     */
    public function encryptString(string $value_to_be_encrypted)
    {
        $newEncrypter = new \Illuminate\Encryption\Encrypter(\config::get('constants.custom_encryption_key'), \config::get('app.cipher'));
        return $newEncrypter->encrypt($value_to_be_encrypted);
    }

    /**
     * Decrypt string
     * @param string
     * @return string
     * @author Gopal Panadi
     */
    public function decryptString(string $value_to_be_decrypted)
    {
        $newEncrypter = new \Illuminate\Encryption\Encrypter(\config::get('constants.custom_encryption_key'), \config::get('app.cipher'));
        return $newEncrypter->decrypt($value_to_be_decrypted);
    }
}
