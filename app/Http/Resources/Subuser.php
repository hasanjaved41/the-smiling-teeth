<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * class Subuser as resource
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class Subuser extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function toArray($request)
    {
        return [
            'subuser_id' => $this->subuser_id,
            'username' => $this->username,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'created_at' => $this->created_at,
            'gcm_id' => $this->gcm_id,
            'phone_number' => $this->phone_number,
            'pc_subscriber_id' => $this->pc_subscriber_id,
            'is_guest_user_access' => $this->is_guest_user_access,
            'is_active' => $this->is_active,
            'employment_status' => $this->employment_status,
            'agency_id' => $this->agency_id,
            'has_panel_access' => $this->has_panel_access,
            'department' => $this->department,
            'sales_role' => $this->sales_role,
            'report_to' => $this->report_to,
            'can_update_payment' => $this->can_update_payment,
            'is_suspended' => $this->is_suspended,
            'has_collection_access' => $this->has_collection_access,
            'has_invoice_access' => $this->has_invoice_access,
            'imei_number' => $this->imei_number,
            'mobile_number' => $this->mobile_number,
            'user_reference_number' => $this->user_reference_number,
            'is_onboarding_completed' => $this->is_onboarding_completed,
            'has_collection_panel_access' => $this->has_collection_panel_access,
            'calling_preference' => $this->calling_preference,
            'subuser_bucket' => $this->subuser_bucket,
            'allocation_config_id' => $this->allocation_config_id,
            'is_available' => $this->is_available,
            'subuser_reference_number' => $this->subuser_reference_number,
            'auto_dialer_data' => $this->auto_dialer_data,
            'dialer' => $this->dialer,
            'advertising_id' => $this->advertising_id,
            'advertising_id_updated_at' => $this->advertising_id_updated_at,
            'field_executive_tl'      => $this->report_to ??'',
            'field_executive_tl_name' => $this->field_executive_tl_name??'',
            'dialer_mode' => $this->dialer_mode??'BOTH',
            'has_contact_access' => $this->has_contact_access??'0',
            'role_title' => (!empty($this->roles)) ? $this->roles->role : null,
            'permission' => (!empty($this->roles)) ? $this->roles->permission : null,
        ];
    }
}
