<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * class SubuserOtp extends Model
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class SubuserOtp extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subuser_otp';


    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $guarded = [];

}
