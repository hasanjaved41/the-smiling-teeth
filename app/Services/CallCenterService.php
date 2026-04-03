<?php

namespace App\Services;

use App\Helpers\AppLogHelper as AppLog;
use App\Helpers\UtilHelper;
use App\Repositories\CallCenterRepository;
use App\Services\Traits\ResponseCodeTrait;
use Carbon\Carbon;
use App\Exceptions\KisshtErrorException;
use App\Services\SubUserService;

/**
 * Class CallCenterService
 * @package App\Services
 * @author Rohan Parkar <rohan.parkar@kissht.com>
 * @since 1.0.0
 */
class CallCenterService
{
    use ResponseCodeTrait;

    protected $call_center_repository;
    protected $subuser_service;
    private $class_name = 'CallCenterService';

    public function __construct(CallCenterRepository $call_center_repository, SubUserService $subuser_service)
    {
        $this->call_center_repository = $call_center_repository;
        $this->subuser_service        = $subuser_service;
    }

    /**
     * Validation rules for send otp functionality
     * @param array $required_fields
     * @return array $rules
     * @author Rohan Parkar <rohan.parkar@kissht.com>
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
     * Get attendance approve response.
     * @param array $data
     * @return reponse
     * @throws \App\Exceptions\ValidationFailedException
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     *
     */
    public function addCallCenterLog(array $data)
    {
        AppLog::info($this->class_name, 'addCallCenterLog', 'Add call center log');

        $checked_in  = Carbon::parse($data['call_initiate_time_date']);
        $checked_out = Carbon::parse($data['call_end_time_date']);

        $call_length = $checked_in->diffInSeconds($checked_out);
        $data['call_length'] = $call_length;

        $result = $this->call_center_repository->addCallLog($data);
        if ($result) {
            return "Call log added successfully!";
        } else {
            return "Record already exists";
        }
    }

    /**
     * Gets the call center log.
     *
     * @param      string  $call_reference_id
     * @author     Rohan PArkar <rohan.parkar@kissht.com>
     * @return     response
     */
    public function getCallCenterLog(string $call_reference_id)
    {
        $callLogData = $this->call_center_repository->findOrFailByReference($call_reference_id);
        if ($callLogData) {
                $insertArr = [
                    'subuser_reference_number' => $callLogData->subuser_reference_number,
                    'user_reference_number' => $callLogData->user_reference_number,
                    'call_reference_id' => $callLogData->call_reference_id
                ];
            $status = $this->subuser_service->addSubuserRatings($insertArr, true);
            if ($status) {
                return $callLogData;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    /**
     * Gets the QC call center log.
     *
     * @param      array  $params
     * @author     Rohan PArkar <rohan.parkar@kissht.com>
     * @return     response
     */
    public function getQcCallLogs(array $params)
    {   
        $query_params = [];
        $return_response = [];
        $date    = Carbon::now(); 
        $query_params['from'] = (isset($params['from']) ? Carbon::parse($params['from'])->copy()->startOfDay() : $date->copy()->startOfDay());
        $query_params['to'] =   (isset($params['to']) ? Carbon::parse($params['to'])->copy()->endOfDay() : $date->copy()->endOfDay());
        $query_params['direction'] = $params['direction'] ?? 'desc';
        $query_params['limit'] = $params['perpage'] ?? 50;
        $query_params['offset'] = $params['offset'] ?? 0;
        $query_params['page_no'] = $params['page_no'] ?? 1;
        $query_params['start'] = $params['start'] ?? null;
        $query_params['end'] = $params['end'] ?? null;
        $query_params['call_center_user_id'] = $params['subuser_id'] ?? null;

        $query_response = $this->call_center_repository->getQcCallLogs($query_params);
        $result = $query_response->get();
        if (count($result) > 0) {
            $return_response['call_logs'] = $result;
            $pagination_data = $query_response->paginate($query_params['limit'], ['*'], '', $query_params['page_no']);
            $return_response['meta'] = [
                'total' => $pagination_data->total(),
                'total_rows' => $pagination_data->count(),
                'per_page' => $pagination_data->perPage(),
                'start' => $pagination_data->firstItem(),
                'end' => $pagination_data->lastItem(),
                'from' => $pagination_data->firstItem(),
                'to' => $pagination_data->lastItem(),
            ];
        }
        
        return $return_response;
    }
}