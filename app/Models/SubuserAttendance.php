<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubuserAttendance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "tbl_fastbanking_subusers_attendance";

    /**
     * The primary key associated with table.
     *
     * @var string
     */
    protected $primaryKey = "subusers_attendance_id";

    protected $guarded = [];

    public function subuserAttendance()
    {
        return $this->hasOne(Subuser::class, 'subuser_reference_number', 'subuser_reference_number');
    }
}
