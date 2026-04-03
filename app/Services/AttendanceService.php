<?php

namespace App\Services;

use App\Helpers\AppLogHelper as AppLog;
use App\Helpers\UtilHelper;
use App\Repositories\AttendanceRepository;
use App\Repositories\SubuserRepository;
use App\Services\Traits\ResponseCodeTrait;
use Carbon\Carbon;
use PhpParser\Node\Expr\Cast\Array_;
use App\Exceptions\KisshtErrorException;

/**
 * Class AttendanceService
 * @package App\Services
 * @author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
 * @since 1.0.0
 */
class AttendanceService
{
    use ResponseCodeTrait;

    protected $attendance_repository;
    private $class_name = 'AttendanceService';
    private $current_date;
    public function __construct(AttendanceRepository $attendance_repository, SubuserRepository $subuser_repository)
    {
        $this->attendance_repository = $attendance_repository;
        $this->subuser_repository    = $subuser_repository;

        ##get current date
        $this->current_date = Carbon::now();
    }

    /**
     * Validation rules for send otp functionality
     * @param array $required_fields
     * @return array $rules
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
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
     * Get attendance subuser ID response.
     * @param array $data
     * @return reponse
     * @throws \App\Exceptions\ValidationFailedException
     * @author yogesh vishwakarma <yogesh.vishwakarma@kissht.com>
     *
     */
    public function findByReference(string $subuser_reference_number)
    {
        ##check if record exist or not
        AppLog::info($this->class_name, 'findByReference', 'Check if record exist or not.');
        return $this->attendance_repository->findByReference($subuser_reference_number);
    }

    /**
     * Get attendance marking response.
     * @param array $data
     * @return reponse
     * @throws \App\Exceptions\ValidationFailedException
     * @author yogesh vishwakarma <yogesh.vishwakarma@kissht.com>
     *
     */
    public function markAttendance($data)
    {

        ##check last attendance date
        AppLog::info($this->class_name, 'markAttendance', 'Mark user attendeance');

        /* Store image on s3 storage */
        $path = $data['selfie_path']->store('subuser_attendance/' . $data['subuser_reference_number'], 's3');

        $data['is_approved'] = 0;
        $data['selfie_path'] = $path;
        $last_attendance     = $this->attendance_repository->lastAttendance($data, $this->current_date->format('Y-m-d'));

        if (empty($last_attendance['created_at'])) {
            $mark_attendance = $this->attendance_repository->create($data);
            $response        = $mark_attendance;
        } else {
            $response = $last_attendance;
        }
        return $response;
    }

    /**
     * Get attendance approve response.
     * @param array $data
     * @return reponse
     * @throws \App\Exceptions\ValidationFailedException
     * @author yogesh vishwakarma <yogesh.vishwakarma@kissht.com>
     *
     */
    public function approveAttendence(array $data)
    {
        AppLog::info($this->class_name, 'approveAttendence', 'Approve user attendeance for user');
        $updateData = [
            'is_approved' => $data['is_approved'],
            'approved_by' => $data['subuser_id'],
            'approved_at' => $this->current_date->format('Y-m-d H:i:s'),
        ];
        $record = [
            'subusers_attendance_id' => $data['attendance_id'],
            'reporting_subuser_id' => $data['subuser_id'],
            'subuser_reference_number' => $data['field_subuser_reference_number']
         ];
        
        ##check if record exist or not
        AppLog::info($this->class_name, 'approveAttendence', 'Check if record exist or not.');
        $checkRecord = $this->attendance_repository->checkRecord($record);
         
        ##call attendance repositiry to update nd approve attendance
        AppLog::info($this->class_name, 'approveAttendence', 'approve attendence of subuser');
        $response = $this->attendance_repository->approveAttendence($updateData,$data);
        if($response){
            $response = "Attendance Approved";
        }
        else{
            throw new KisshtErrorException("Failed to update Record", 102);
        }
        return $response;

    }

    /**
     * Get last attendance mark response.
     * @param array $subuser_reference_number
     * @return reponse
     * @throws \App\Exceptions\ValidationFailedException
     * @author yogesh vishwakarma <yogesh.vishwakarma@kissht.com>
     *
     */
    public function lastAttendence(string $subuser_reference_number)
    {
        $response = $this->attendance_repository->lastAttendence($subuser_reference_number);
        if (isset($response['selfie_path'])) {
            $response['selfie_path'] = UtilHelper::awsFilePath($response['selfie_path'], 15);
        }
        return $response;

    }

    /**
     * get attendance status
     *
     * @param      string  $subuser_reference_number  The subuser reference number
     *
     * @return     Array
     * @author     Rohan Parkar
     */
    public function attendanceStatus(string $subuser_reference_number)
    {
        AppLog::info($this->class_name, 'attendanceStatus', 'Users attendance status');

        $get_attandance_data           = [];
        $subuser_id                    = $this->attendance_repository->findByReference($subuser_reference_number)->subuser_id;
        $subuser_reference_number_list = $this->attendance_repository->getSubuserAttendanceRecords($subuser_id);
        
        $data = [];
        foreach ($subuser_reference_number_list as $key => $value) {
                 $result = [
                    'attendance_id' => $value->subusers_attendance_id,
                    'subuser_id'   => $value->subuser_id,
                    'location'     => "",
                    'login_time'   => ($value->created_at ? $value->created_at : ""),
                    'selfie_path' =>  $value->selfie_path ? UtilHelper::awsFilePath($value->selfie_path, 15) : '',
                    'subuser_name' => $value->name,
                    'is_approved' => $value->is_approved,
                    'subuser_reference_number' => isset($value->subuser_reference_number)?$value->subuser_reference_number:'',
                ];
                
                if ($value->subuser_reference_number == '') {
                    $data['in_active'][] = $result;
                } else {
                    $data['active'][] = $result;
                }
        }

        if (!array_key_exists('in_active', $data)) {
            $data['in_active'] = null;
        }
        if (!array_key_exists('active', $data)) {
            $data['active'] = null;
        }

        return $data;
    }

     /**
     * Get pending attendance response.
     * @param array $subuser_reference_number
     * @return reponse
     * @throws \App\Exceptions\ValidationFailedException
     * @author Javed Hasan <javed.hasan@kissht.com>
     *
     */
    public function pendingAttendence($reporting_subuser_id)
    {
        $pending_attendance = [];
        $pending_attendance = collect($this->attendance_repository->pendingAttendence($reporting_subuser_id));
        if (!empty($pending_attendance)) {
            $pending_attendance->map(function($item, $key){
                $item['selfie_path'] = UtilHelper::awsFilePath($item['selfie_path'], 15);
            }, $pending_attendance);
        }
       return $pending_attendance; 
    }
}
