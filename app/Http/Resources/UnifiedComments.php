<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnifiedComments extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = [
            'user_reference_number' => $this->user_reference_number ?? null,
            'action_type' => $this->action_type ?? null,
            'action_reference_number' => $this->action_reference_number ?? null,
            'sub_action_type' => $this->sub_action_type ?? null,
            'sub_action_type_reference_number' => $this->sub_action_type_reference_number ?? null,
            'added_by_subuser_id' => $this->added_by_subuser_id ?? null,
            'comment' => $this->comment ?? null,
            'disposition' => $this->disposition ?? null,
            'created_at' => date('Y-m-d H:i:s',strtotime($this->created_at)) ?? null,
            'updated_at' => date('Y-m-d H:i:s',strtotime($this->updated_at)) ?? null,
            'follow_up_date' => $this->follow_up_date ?? null,
            'migration_status_drop' => $this->migration_status_drop ?? null,
        ];
        return $resource;
    }
}
