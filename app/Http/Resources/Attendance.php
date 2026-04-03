<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Subuser as SubuserResources;

class Attendance extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $subuser_details = isset($this->subuserAttendance)?$this->subuserAttendance:null;
        $return['subusers_attendance_id'] = $this['subusers_attendance_id'];
        $return['username'] = $subuser_details;
        $return['subuser_reference_number'] = $this['subuser_reference_number'];
        $return['reporting_subuser_id'] = $this['reporting_subuser_id'];
        $return['latitude'] = $this['latitude'];
        $return['longitude'] = $this['longitude'];
        $return['selfie_path'] = $this['selfie_path'];
        $return['is_approved'] = $this['is_approved'];
        $return['approved_by'] = $this['approved_by'];
        $return['created_at'] = date($this['created_at']);
        return $return;
    }
}
