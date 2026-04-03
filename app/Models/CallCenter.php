<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * class CallCenter extends Model
 * @author Rohan Parkar <rohan.parkar@kissht.com>
 * @since 1.0.0
 */
class CallCenter extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_fastbanking_call_center_log_data';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'call_id';

    protected $guarded = [];

    public function subuserRatings()
    {
        return $this->hasOne('App\Models\SubuserRatings','call_reference_id','call_reference_id');
    }

    public function subuser()
    {
        return $this->hasOne('App\Models\Subuser','subuser_reference_number','subuser_reference_number');
    }
}