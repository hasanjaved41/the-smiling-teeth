<?php
namespace App\Services;

use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use App\Repositories\SubuserRepository;
use App\Services\ServiceCalls\AuthServiceCall;
use App\Services\Traits\ResponseCodeTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class LoginService
 * @package App\Services
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class LoginService
{
    use ResponseCodeTrait;

    protected $repository;

    public function __construct(SubuserRepository $subuser_repository)
    {
        $this->repository = $subuser_repository;
    }
    /**
     * Validation rules for send otp functionality
     * @param array $required_fields
     * @return array $rules
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function getLoginRules(array $required_fields = [])
    {
        $rules = [
            'email' => 'exists:tbl_fastbanking_subusers,email',
        ];
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
     * Generate Token
     * @param object $params
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function generateToken(object $params)
    {
        $token_params['client_id'] = $params['client_id'];
        $token_params['reference_number'] = $params['subuser_reference_number'];
        $token_params['reference_type'] = 'subuser';
        $token_params['role_id'] = $params['role'];

        $token_response = AuthServiceCall::generateToken($token_params);

        return $token_response;
    }

    /**
     * Verify User With IMEI Number
     * @param array $response
     * @param array $data
     * @return string
     * @throws ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function verifyUserWithImeiNumber($response, $data)
    {
        if ($response['imei_number'] == null && $response['advertising_id'] == null) {
            if ($data['is_advertising_id']) {
                $field = 'advertising_id';
                /*check email is registerd with given email by advertising_id field*/
                $records = $this->repository->checkEmailWithImei($field, $data['imei_number']);
            } else {
                $field = 'imei_number';
                /*check email is registerd with given email by imei_number field*/
                $records = $this->repository->checkEmailWithImei($field, $data['imei_number']);
            }

            if($records == null){
                if ($data['is_advertising_id']) {
                    /*update in advertising_id field*/
                    $update_data = ['advertising_id' => $data['imei_number'], 'imei_number' => null, 'advertising_id_updated_at' => Carbon::now()];
                } else {
                    /*update in imei_number filed*/
                    $update_data = ['advertising_id' => null, 'imei_number' => $data['imei_number'], 'advertising_id_updated_at' => Carbon::now()];
                }
                $this->repository->update($response['subuser_reference_number'], $update_data);
                return true;
            } else {
                throw new ValidationFailedException('Device is not registered with given email.');
            }
        } else {
            if ($data['is_advertising_id']) {
                if ($response['advertising_id'] == $data['imei_number']) {
                    return true;
                } else {
                    throw new ValidationFailedException('Device is not registered with given email.');
                }
            } else {
                if ($response['imei_number'] == $data['imei_number']) {
                    return true;
                } else {
                    throw new ValidationFailedException('Device is not registered with given email.');
                }
            }
        }
    }
}