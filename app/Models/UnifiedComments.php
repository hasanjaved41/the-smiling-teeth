<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * class UnifiedCommets extends Model
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class UnifiedComments extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_fastbanking_unified_comments';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
