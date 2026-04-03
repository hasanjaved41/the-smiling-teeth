<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubuserAttributes extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $name = explode(' ', $this['name']);
        $first_name = $name[0] ?? NULL;
        $last_name = $name[1] ?? NULL;

        $due_date_formatted = NULL;
        if ($this['due_date'] != NULL) {
            $time = strtotime($this['due_date']);
            $due_date_formatted = date('d-m-Y', $time);
        }

        $allocation_date_formatted = NULL;
        if ($this['allocation_date'] != NULL) {
            $time = strtotime($this['allocation_date']);
            $allocation_date_formatted = date('d-m-Y', $time);
        }

        $data = [
            'sub_user_reference_number' => $this['subuser_reference_number'] ?? NULL,
            'firstName' => $first_name ?? NULL,
            'lastName' => $last_name ?? NULL,
            'email' => $this['email'] ?? NULL,
            'phone' => $this['mobile_number'] ?? NULL,
            'attributes' => [
                'employment_status' => $this['employment_status'] ?? NULL,
                'agency_id' => $this['agency_id'] ?? NULL,
                'is_active' => $this['is_active'] ?? NULL,
                'is_suspended' => $this['is_suspended'] ?? NULL,
                'role_id' => $this['role'] ?? NULL,
                'reporting_person' => $this['report_to_data']['name'] ?? NULL,
                'reporting_person_email' => $this['report_to_data']['email'] ?? NULL,
                'reporting_person_mobile' => $this['report_to_data']['mobile_number'] ?? NULL,
                'reporting_person_phone' => $this['report_to_data']['phone_number'] ?? NULL,
                'subuser_bucket' => $this['subuser_bucket'] ?? NULL,
                'current_assigned_cases' => $this['current_assigned_cases'] ?? NULL,
                'untouched_cases' => $this['untouched_cases'] ?? NULL,
                'cycle_target' => $this['cycle_target'] ?? NULL,
                'current_paid_cases' => $this['current_paid_cases'] ?? NULL,
                'to_be_collected_cases' => $this['to_be_collected_cases'] ?? NULL,
                'to_be_achieved_cases' => $this['to_be_achieved_cases'] ?? NULL,
                'due_date' => $due_date_formatted,
                'allocation_date' => $allocation_date_formatted,
                'days_in_cycle' => $this['days_in_cycle'] ?? NULL,
                'days_left_in_cycle' => $this['days_left_in_cycle'] ?? NULL,
                'drr' => $this['drr'] ?? NULL,
                'paid_count_today' => $this['paid_count_today'] ?? NULL,
                'ptp_count_today' => $this['ptp_count_today'] ?? NULL
            ]
        ];

        return $data;
    }
}
