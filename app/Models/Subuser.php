<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * class Subuser extends Model
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class Subuser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_fastbanking_subusers';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'subuser_id';

    protected $guarded = [];


    public function roles()
    {
        return $this->belongsTo('App\Models\Role','role','role_id');
    }
    
}
