<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubuserRatings extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "subuser_rating";

    protected $guarded = [];

    protected $fillable = [
    	'call_reference_id',
    	'subuser_reference_number',
    	'user_reference_number',
    	'rating',
    	'rating_content',
    	'rating_created_at',
    	'qc_by_subuser_reference_number',
    	'qc_rating',
    	'qc_rating_content',
    	'qc_rating_created_at'
    ];
}
