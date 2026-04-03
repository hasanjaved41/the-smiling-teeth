<?php

namespace App\Repositories;

use App\Models\SubuserOtp;

/**
 * Class OtpRepository
 * @package App\Repositories
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class OtpRepository
{
    /**
     * @var Sub user Otp $model
     */
    protected $model;

    public function __construct(SubuserOtp $otp)
    {
        $this->model = $otp;
    }

    /**
     * create resource
     * @param  array $params
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function create(array $params)
    {
        return $this->model->create($params);
    }

    /**
     * update resource
     * @param  array $params
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function update(array $params, $mobile_number)
    {
        $entity = $this->model->where('mobile_number', $mobile_number)->first();

        if (!empty($entity)) {
            return $entity->update($params);
        }
    }

    /**
     * get otp details
     * @param  $mobile_number
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getOtp($mobile_number)
    {
        $current_datetime = date("Y-m-d H:i:s");
        return $this->model->select('*')->where('mobile_number', $mobile_number)->where('otp_expiry', '>=', $current_datetime)->first();
    }

    /**
     * verify otp details
     * @param  $mobile_number
     * @param  $otp
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function verifyOtp($mobile_number, $otp)
    {
        $current_datetime = date("Y-m-d H:i:s");

        return $this->model->select('id')->where('mobile_number', $mobile_number)->where('otp',
            $otp)->where('otp_expiry', '>=', $current_datetime)->first();
    }

    /**
     * delete otp details after verification
     * @param  $mobile_number ,$otp
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function deleteOtp($mobile_number,$otp)
    {
        return $this->model->where('mobile_number', $mobile_number)->where('otp',
            $otp)->delete();
    }
}
