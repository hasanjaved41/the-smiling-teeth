<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CallCenterLog extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {   
       return [
            'subuser_reference_number' => $this->subuser_reference_number,
            'user_reference_number' => $this->user_reference_number,
            'customer_number' => $this->customer_number,
            'call_center_user_name' => $this->call_center_user_name,
            'customer_name' => $this->customer_name,
            'call_length' => $this->call_length,
            'call_status' => $this->call_status,
            'recording_path' => $this->recording_path,
            'call_reference_id' => $this->call_reference_id,
            'call_center_user_id' => $this->call_center_user_id,
            'qc_ratings' => $this->subuserRatings['qc_rating']
       ];
    }
}
