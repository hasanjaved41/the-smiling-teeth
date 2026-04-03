<?php

namespace App\Repositories;

use App\Models\CallCenter;

/**
 * Class CallCenterRepository
 * @package App\Repositories
 * @author Rohan Parkar <rohan.parkar@kissht.com>
 * @since 1.0.0
 */
class CallCenterRepository
{
    protected $call_center_model;

    /**
     * Constructs a new instance.
     *
     * @param      \App\Models\CallCenter  $call_center_model  The call center model
     * @author         Rohan PArkar
     */
    public function __construct(CallCenter $call_center_model)
    {
        $this->call_center_model = $call_center_model;
    }

    /**
     * Adds a call log.
     *
     * @param      $data
     *
     * @return     status
     * @author     Rohan PArkar
     */
    public function addCallLog($data)
    {
        $status = 0;
        $query  = $this->call_center_model->where('call_reference_id', $data['call_reference_id'])->first();
        if (!$query) {
            $this->call_center_model->insert($data);
            $status = 1;
        }
        return $status;
    }

    /**
     * @param string $reference_number
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function findOrFailByReference(string $call_reference_id)
    {
        return $this->call_center_model->where('call_reference_id', $call_reference_id)
            ->first();
    }

    /**
     * @param string $array
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function getQcCallLogs(array $params)
    {   
        $start = $params['start'];
        $end = $params['end'];
        $query = $this->call_center_model->select('call_center_user_name','subuser_reference_number','customer_name','user_reference_number','call_length','call_status','recording_path','call_reference_id','call_center_user_id')
            ->where('call_status', 'ANSWER')
            ->where('call_type', 'collection_Call')
            ->where('calling_mode', 'KALEYRA')
            ->whereBetween('created_at', [$params['from'], $params['to']])
            ->limit($params['limit'])->offset($params['offset'])
            ->orderBy('created_at',$params['direction'])->whereHas('subuser', function($query){
                    $query->where('employment_status','!=','AGENCY');
            });

        if (!is_null($start) && !is_null($end)) {
            $query->whereRaw("call_length between $start and $end");
        }

        if (!is_null($params['call_center_user_id'])) {
            $query->where('call_center_user_id', $params['call_center_user_id']);
        }
        
        return $query;
    }
}
