<?php

namespace App\Repositories;

use App\Models\SubuserRatings;
use Carbon\Carbon;
use DB;

class SubuserRatingsRepository {

	protected $model;

    public function __construct(SubuserRatings $subuser_ratings)
    {
        $this->model = $subuser_ratings;
    }

    /**
     * add ratings
     *
     * @param      array  $data   
     */
    public function create(array $data)
    {   
        return $this->model->create($data);
    }

     /**
     * @param string $reference_number
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function findByReference(string $call_reference_id)
    {
        return $this->model->where('call_reference_id', $call_reference_id)->first();
    }

    /**
     * @param string $reference_number
     * @return Model|null
     * @throws ModelNotFoundException
     */
    public function findByReferenceWithCondition(string $call_reference_id)
    {
        return $this->model->where('call_reference_id', $call_reference_id)
        				   ->whereRaw("HOUR(TIMEDIFF(NOW(), created_at)) < 48")
        				   ->whereNull('rating')
        				   ->first();
    }

    /**
     * Update Ratings
     * @param array $data
     * @param array $data
     * @return Object
     */
    public function update(array $data)
    {
        return $this->model->where('call_reference_id', $data['call_reference_id'])
        				   ->update($data);        
    }
}