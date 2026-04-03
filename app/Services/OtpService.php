<?php
namespace App\Services;

use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use App\Repositories\OtpRepository;
use App\Services\ServiceCalls\CommunicationServiceCall;
use App\Services\Traits\ResponseCodeTrait;

/**
 * Class OtpService
 * @package App\Services
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class OtpService
{
    use ResponseCodeTrait;

    protected $expiry_length;
    private $class_name = 'OtpService';
    private $otp_repository;

    public function __construct(OtpRepository $otp_repository) {
        $this->otp_repository = $otp_repository;
        $this->expiry_length  = "10";
    }

    /**
     * Send otp to mobile number
     *
     * @param string $mobile_number, $login_source
     * @return mixed
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function sendOtp($mobile_number, $login_source)
    {
        if (config('app.env') != 'production') {
            $response = $this->getResponseCode(1);
            return $response;
        }
        $response = $this->generateOtp($mobile_number, $login_source);
        return $response;
    }

    /**
     * Generate OTP
     *
     * @param string $mobile_number, $login_source
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @since 1.0.0
     */
    public function generateOtp($mobile_number, $login_source)
    {
        $exists_otp = $this->otp_repository->getOtp($mobile_number);
        if (!empty($exists_otp)) {
            $otp            = $exists_otp['otp'];
            $sent_otp_count = $exists_otp['sent_otp_count'];
            if ($sent_otp_count < 5) {
                $communication_data = array(
                    'otp'           => $otp,
                    'mobile_number' => $mobile_number,
                    'login_source' => $login_source,
                );
                $this->send_otp_to_communication_engine($communication_data);
                /*response from communication service*/
                $update_data = [
                    'otp_expiry'     => date("Y-m-d H:i:s", strtotime("+ " . $this->expiry_length . " mins")),
                    'sent_otp_count' => $sent_otp_count + 1
                ];
                $this->otp_repository->update($update_data, $mobile_number);
                $response = $this->getResponseCode(1);
            } else {
                $current_datetime = strtotime(date("Y-m-d H:i:s"));
                $expiry_time      = strtotime($exists_otp['otp_expiry']);
                $after_minutes    = ceil(round(abs($expiry_time - $current_datetime) / 60, 2));
                $message  = 'You have reached maximum number for OTP.Please try after ' . $after_minutes . ' minutes';
                $response = $this->getResponseCode(101);
                $response['message'] = $message;
            }
        } else {
            $otp_details = $this->createOtp();

            $data['otp']           = $otp_details['generated_otp'];
            $data['otp_expiry']    = $otp_details['otp_expiry'];
            $data['mobile_number'] = $mobile_number;

            $res = $this->otp_repository->create($data);
            AppLog::debug($this->class_name,'generateOtp','Otp Created',$res->toArray());

            $communication_data = array(
                'otp'           => $otp_details['generated_otp'],
                'mobile_number' => $mobile_number,
                'login_source' => $login_source,
            );

            $this->send_otp_to_communication_engine($communication_data);
            /* response from communication service*/
            $response = $this->getResponseCode(1);
        }
        return $response;
    }

    /**
     * Create OTP
     *
     * @param
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @since 1.0.0
     */
    private function createOtp()
    {
        $length = "6";
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        $generated_otp = $randomString;
        $otp_expiry    = date("Y-m-d H:i:s", strtotime("+" . $this->expiry_length . " mins"));

        $data = array(
            'generated_otp' => $generated_otp,
            'otp_expiry'    => $otp_expiry
        );
        return $data;
    }

    /**
     * Communication service call for send otp sms
     *
     * @param array $communication_data
     * @return string
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @since 1.0.0
     */
    private function send_otp_to_communication_engine($communication_data)
    {
        $otp                  = $communication_data['otp'];
        $mobile_number        = $communication_data['mobile_number'];
        $login_source         = ($communication_data['login_source'] ==
                                config('loginsource.collection_panel'))
                                ? 'Collection'
                                : 'Admin';
        $message              = "Your ".$login_source." panel OTP for Login: $otp.";

        $params['contactNo']  = $mobile_number;
        $params['smsContent'] = $message;

        $postData = $params;
        $postData = json_encode($postData);

        $communication_service_response = CommunicationServiceCall::sendOtp($postData);
        return $communication_service_response;
//        if (config('app.env') == 'development') {
//            $communication_service_response = CommunicationServiceCall::sendOtp($postData);
//            return $communication_service_response;
//        } else {
//            return true;
//        }
    }

    /**
     * Verify OTP for given mobile number.If OTP verified then delete verified OTP from table
     * @param int $mobile_number number mandatory parameter
     * @param int $otp mandatory parameter
     * @return boolean
     * @throws \App\Exceptions\ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @since 1.0.0
     */
    public function verifyOtp($mobile_number, $otp)
    {
        if (config('app.env') != 'production' && $otp == '888888') {
            return true;
        }
        $records = $this->otp_repository->verifyOtp($mobile_number, $otp);

        if (!empty($records)) {
            $this->otp_repository->deleteOtp($mobile_number, $otp);
            return true;
        } else {
            AppLog::debug($this->class_name, 'verifyOtp', 'Incorrect OTP.');
            throw new ValidationFailedException('Incorrect OTP.', 101);
        }
    }
}