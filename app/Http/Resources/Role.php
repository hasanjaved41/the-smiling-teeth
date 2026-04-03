<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Role extends JsonResource
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
            'role_id' => $this['role_id'],
            'role_title' => $this['role'],
            'created_at' => date('Y-m-d H:m:s',strtotime($this['created_at'])),
        ];
    }
}
