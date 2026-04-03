<?php

namespace App\Repositories;

use App\Models\Subuser;
use App\Models\SubuserAttendance;
use Carbon\Carbon;
use DB;

/**
 * Class AttendanceRepository
 * @package App\Repositories
 * @author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
 * @since 1.0.0
 */
class AttendanceRepository
{
    protected $attendance_model;
    protected $subuser_model;

    public function __construct(SubuserAttendance $attendance_model, Subuser $subuser_model)
    {
        /**
         * calling attendence model
         * @var \App\Models\SubuserAttendance
         * @Author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
         */
        $this->attendance_model = $attendance_model;
        $this->subuser_model    = $subuser_model;
    }

    public function findByReference(string $subuser_reference_number)
    {
        return $this->subuser_model->where('subuser_reference_number', $subuser_reference_number)->firstOrFail();
    }
    /**
     * getting last attendance data of subusers
     * @var \App\Models\SubuserAttendance
     * @Author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     */
    public function lastAttendance($data, $current_date)
    {
        return $this->attendance_model->where('subuser_reference_number', $data['subuser_reference_number'])->where('created_at', 'like', $current_date . '%')->orderBy('created_at', 'DESC')->first();

    }

    /**
     * create new record
     * @var \App\Models\SubuserAttendance
     * @Author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     */
    public function create($data)
    {
        return $this->attendance_model->create($data);
    }

    /**
     * check record
     * @var \App\Models\SubuserAttendance
     * @Author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     */
    public function checkRecord($data)
    {
        return $this->attendance_model->where($data)->firstOrFail();
    }

    /**
     * approve attendance
     * @var \App\Models\SubuserAttendance
     * @Author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     */
    public function approveAttendence($updateData, $data)
    {

        return $this->attendance_model->where('subusers_attendance_id', $data['attendance_id'])->where('reporting_subuser_id', $data['subuser_id'])->where('subuser_reference_number', $data['field_subuser_reference_number'])->update($updateData);
    }

    /**
     * last attendance
     * @var \App\Models\SubuserAttendance
     * @Author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     */
    public function lastAttendence(string $subuser_reference_number)
    {  
       return $this->attendance_model->where('subuser_reference_number', $subuser_reference_number)->orderBy('subusers_attendance_id', 'desc')->first();
    }

    /**
     * pending attendance
     * @var \App\Models\SubuserAttendance
     * @Author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function getSubuserAttendanceRecords($subuser_id)
    {  
        return DB::table('tbl_fastbanking_subusers as su')->select('sa.subusers_attendance_id','su.subuser_id', 'sa.subuser_reference_number', 'sa.created_at', 'su.name','sa.selfie_path','sa.is_approved')
            ->leftJoin('tbl_fastbanking_subusers_attendance as sa', function($query) {
                $query->on('su.subuser_reference_number','=','sa.subuser_reference_number')
                      ->where('sa.created_at','>=',Carbon::today());
            })->where('su.report_to', $subuser_id)->get();
    }

    /**
     * pending attendance
     * @var \App\Models\SubuserAttendance
     * @Author Javed Hasan <javed.hasan@kissht.com>
     */
    public function pendingAttendence($reporting_subuser_id)
    {   
        return $this->attendance_model->where('reporting_subuser_id', $reporting_subuser_id)->where('is_approved',0)->get();
    }
}

