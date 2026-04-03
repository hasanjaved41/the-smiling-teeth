<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * class Subuser rating as resource
 * @author Rohan PArkar <rohan.parkar@kissht.com>
 * @since 1.0.0
 */
class SubuserRating extends JsonResource
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
            'user_reference_number' => $this->user_reference_number
        ];
    }
}
