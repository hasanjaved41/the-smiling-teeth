<?php

namespace App\Services;

use App\Exceptions\KisshtErrorException;
use App\Exceptions\ServiceCallFailedException;
use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use App\Helpers\UtilHelper;
use App\Http\Resources\Subuser as SubuserResource;
use App\Http\Resources\SubuserAttributes as SubuserAttributesResource;
use App\Repositories\SubuserAtrributesRepository;
use App\Repositories\SubuserRepository;
use App\Repositories\SubuserRatingsRepository;
use App\Repositories\SubuserRoleRepository;
use App\Http\Resources\SubuserRole as SubuserRoleResource;
use App\Http\Resources\Role as RoleResource;
use App\Services\ServiceCalls\CollectionsServiceCall;
use App\Services\Traits\ResponseCodeTrait;
use Illuminate\Support\Facades\Response;
use App\Services\ServiceCalls\CommunicationServiceCall;
use Carbon\Carbon;
use Exception;

/**
 * Class SubUserService
 * @package App\Services
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class SubUserService
{
    use ResponseCodeTrait;

    protected $repository;
    protected $subuser_attributes_repository;
    protected $subuser_ratings_repository;
    protected $subuser_roles_repository;

    private $class_name = '$SubUserService';

    public static $default_subuser_attributes = [];


    public function __construct(SubuserRepository $subuser_repository, SubuserAtrributesRepository $subuser_attributes_repository,
                                SubuserRatingsRepository $subuser_ratings_repository, SubuserRoleRepository $subuser_roles_repository)
    {
        $this->repository = $subuser_repository;
        $this->subuser_attributes_repository = $subuser_attributes_repository;
        $this->subuser_ratings_repository = $subuser_ratings_repository;
        $this->subuser_roles_repository = $subuser_roles_repository;
    }

    /**
     * Validation rules for send otp functionality
     * @param array $required_fields
     * @return array $rules
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function getRules(array $required_fields = [])
    {
        $rules = [
            'email' => 'exists:tbl_fastbanking_subusers,email',
            'client_id' => 'exists:tbl_fastbanking_auth_clients,client_id',
            'subuser_reference_field' => 'exists:tbl_fastbanking_subusers,subuser_reference_field',
            'name' => 'min:3'
        ];
        foreach ($required_fields as $field) {
            if (isset($rules[$field])) {
                $rules[$field] = 'required|' . $rules[$field];
            } else {
                $rules[$field] = 'required';
            }
        }
        return $rules;
    }

    /**
     * Get mobile number by email.
     * @param string $email
     * @return array
     * @throws \App\Exceptions\ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function getMobileNumber(string $email)
    {
        $response = $this->repository->findByEmail($email);
        if ($response['is_active']) {
            if ($response['mobile_number']) {
                return $response;
            } else {
                AppLog::debug($this->class_name, 'getMobileNumber', 'Mobile number does not exist.');
                throw new ValidationFailedException('Mobile number does not exist with this email id.', 101);
            }
        } else {
            AppLog::debug($this->class_name, 'getMobileNumber', 'Inactive User.');
            throw new ValidationFailedException('Inactive User.', 101);
        }
    }

    /**
     * Verify password
     * @param string $email
     * @param string $password
     * @return Response
     * @throws \App\Exceptions\ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function verifyPassword(string $email, string $password)
    {
        $user = $this->repository->findByEmail($email);

        if ($user['is_active']) {
            if (md5($password) == $user['password']) {
                return true;
            } else {
                AppLog::debug($this->class_name, 'verifyPassword', 'Incorrect password.');
                throw new ValidationFailedException('Incorrect Password.', 101);
            }
        } else {
            AppLog::debug($this->class_name, 'verifyPassword', 'Inactive User.');
            throw new ValidationFailedException('Inactive User.', 101);
        }

    }

    /**
     * Get Refund Data using Payment Reference Number
     * @param array $data
     * @return object
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getSubuserDetails(array $data)
    {
        $subuser_response = $this->repository->findOrFailByReference($data['subuser_reference_number']);

        $subuser_tl_data = $this->repository->getReportToDetails($subuser_response['report_to']);
        $subuser_response['field_executive_tl_name'] = $subuser_tl_data['name'];

        return $subuser_response;
    }

    /**
     * Get Subusers data
     * @param array $condition
     * @param $limit
     * @param $offset
     * @return object
     * @author Neha G <neha.gupta@kissht.com>
     */
    public function getSubuserListing(array $condition, $offset, $limit)
    {
        $subuser_response = $this->repository->getSubuserByCondition($condition, $offset, $limit);

        return $subuser_response;
    }

    /**
     * Check email has access
     * @param  $data , $user_data
     * @return boolean
     * @throws \App\Exceptions\ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function hasAccess(array $data, $user_data)
    {
        switch ($data['login_source']) {
            case 'collection_app':
                $has_access = $this->checkHasAccess($user_data['has_collection_access']);
                break;
            case 'collection_panel':
                $has_access = $this->checkHasAccess($user_data['has_collection_panel_access']);
                break;
            default:
                $has_access = true;
                break;
        }
        if ($has_access) {
            return $has_access;
        } else {
            AppLog::debug($this->class_name, 'hasAccess', 'Check has access');
            throw new ValidationFailedException('Unauthorized access', 101);
        }
    }


    /**
     * Check email has access
     * @param  $access
     * @return boolean
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function checkHasAccess($access)
    {
        if ($access) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $subuser_reference_number
     * @param $params
     * @return mixed
     * @throws ValidationFailedException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getSubuserAttributes($subuser_reference_number, $params)
    {
        $response = null;
        $subuser_params_force = (!empty($params) && !empty($params['force'])) ? true : false;

        if ($subuser_params_force === false) {
            $subuser_resource = null;

            $subuser_data = $this->repository->findByReference($subuser_reference_number);

            if (!empty($subuser_data)) {
                $subuser_data = $subuser_data->toArray();
                $subuser_attribute_data = $this->subuser_attributes_repository->findByField('subuser_reference_number', $subuser_reference_number);
                $subuser_attribute_data = (!empty($subuser_attribute_data)) ? $subuser_attribute_data->toArray() : [];
                $subuser_tl_data = $this->repository->getReportToDetails($subuser_data['report_to']);
                $subuser_tl_data = (!empty($subuser_tl_data)) ? $subuser_tl_data->toArray() : [];
                $subuser_resource = UtilHelper::arrayMergeIfNotNull($subuser_attribute_data, $subuser_data, $subuser_tl_data);
                $subuser_resource['report_to_data'] = $subuser_tl_data;

                if (!empty($subuser_resource)) {
                    $response = new SubuserAttributesResource($subuser_resource);
                }
            }
            return $response;
        } else {
            try {
                $response = $this->processSubUserAttributes($subuser_reference_number);
                return $response;
            } catch (\Exception $e) {
                throw new ValidationFailedException('SubUserService > getSubUserAttributes > error : ' . $e->getMessage());
            }
        }
    }

    /**
     * @param $subuser_reference_number
     * @return mixed
     * @throws ValidationFailedException
     * @throws ServiceCallFailedException
     * @throws KisshtErrorException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    private function processSubUserAttributes($subuser_reference_number)
    {
        /*
        |--------------------------------------------------------------------------
        | Subuser Data
        |--------------------------------------------------------------------------
        */

        $subuser_data = $this->repository->findByReference($subuser_reference_number);
        $subuser_tl_data = $this->repository->getReportToDetails($subuser_data['report_to']);
        $subuser_tl_data = (!empty($subuser_tl_data)) ? $subuser_tl_data->toArray() : [];
        $subuser_resource['report_to_data'] = $subuser_tl_data;

        if (!empty($subuser_data)) {
            $subuser_params['subuser_reference_number'] = $subuser_reference_number;
            $subuser_params['mobile_number'] = $subuser_data->mobile_number ?? NULL;
            $subuser_params['email'] = $subuser_data->email ?? NULL;
            $subuser_params['username'] = $subuser_data->username ?? NULL;
            $subuser_params['employment_status'] = $subuser_data->employment_status ?? NULL;
            $subuser_params['agency_id'] = $subuser_data->agency_id ?? NULL;
            $subuser_params['is_active'] = $subuser_data->is_active ?? NULL;
            $subuser_params['is_suspended'] = $subuser_data->is_suspended ?? NULL;
            $subuser_params['role_id'] = $subuser_data->role_id ?? NULL;
            $subuser_params['reporting_person'] = $subuser_resource['report_to_data']['name'] ?? NULL;
            $subuser_params['reporting_person_email'] = $subuser_resource['report_to_data']['email'] ?? NULL;
            $subuser_params['reporting_person_mobile'] = $subuser_resource['report_to_data']['mobile_number'] ?? NULL;
            $subuser_params['reporting_person_phone'] = $subuser_resource['report_to_data']['phone_number'] ?? NULL;
            $subuser_params['subuser_bucket'] = $subuser_data->subuser_bucket ?? NULL;
        } else {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Subuser Attribute Data
        |--------------------------------------------------------------------------
        */
        $subuser_attribute = $this->subuser_attributes_repository->findByField('subuser_reference_number', $subuser_reference_number);

        /*
        |--------------------------------------------------------------------------
        | Default Data
        |--------------------------------------------------------------------------
        */
        self::$default_subuser_attributes['current_assigned_cases'] = (!empty($subuser_attribute->current_assigned_cases)) ?? NULL;
        self::$default_subuser_attributes['untouched_cases'] = (!empty($subuser_attribute->untouched_cases)) ?? NULL;
        self::$default_subuser_attributes['cycle_target'] = (!empty($subuser_attribute->cycle_target)) ?? NULL;
        self::$default_subuser_attributes['current_paid_cases'] = (!empty($subuser_attribute->current_paid_cases)) ?? NULL;
        self::$default_subuser_attributes['to_be_collected_cases'] = (!empty($subuser_attribute->to_be_collected_cases)) ?? NULL;
        self::$default_subuser_attributes['to_be_achieved_cases'] = (!empty($subuser_attribute->to_be_achieved_cases)) ?? NULL;
        self::$default_subuser_attributes['due_date'] = (!empty($subuser_attribute->due_date)) ?? NULL;
        self::$default_subuser_attributes['allocation_date'] = (!empty($subuser_attribute->allocation_date)) ?? NULL;
        self::$default_subuser_attributes['days_in_cycle'] = (!empty($subuser_attribute->days_in_cycle)) ?? NULL;
        self::$default_subuser_attributes['days_left_in_cycle'] = (!empty($subuser_attribute->days_left_in_cycle)) ?? NULL;
        self::$default_subuser_attributes['drr'] = (!empty($subuser_attribute->drr)) ?? NULL;
        self::$default_subuser_attributes['paid_count_today'] = (!empty($subuser_attribute->paid_count_today)) ?? NULL;
        self::$default_subuser_attributes['ptp_count_today'] = (!empty($subuser_attribute->ptp_count_today)) ?? NULL;

        if (!empty(self::$default_subuser_attributes)) {
            $subuser_params['collections'] = UtilHelper::arrayMergeIfNotNull($subuser_params, self::$default_subuser_attributes);
        }

        $subuser_collection_details_response = CollectionsServiceCall::getCollectionsDetails($subuser_reference_number);

        if (!empty($subuser_collection_details_response) && !empty($subuser_collection_details_response['data'])) {
            if (!empty($subuser_collection_details_response['data']['collections'])) {
                $subuser_params['collections'] = $subuser_collection_details_response['data']['collections'];
            } else {
                $subuser_params['collections'] = 'NONE';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update / Create subuser Attributes Table with new refreshed values
        |--------------------------------------------------------------------------
        */
        $operation = (!empty($subuser_attribute)) ? 'update' : 'create';
        $subuser_attribute_data = $this->updateOrCreateSubUserAttribute($operation, $subuser_params);
        /*
        |--------------------------------------------------------------------------
        | Generate Final Response
        |--------------------------------------------------------------------------
        */
        $subuser_data = $subuser_data->toArray();
        $subuser_attribute_data = $subuser_attribute_data->toArray();
        $subuser_resource = UtilHelper::arrayMergeIfNotNull($subuser_attribute_data, $subuser_data);
        $subuser_resource['report_to_data']['name'] = $subuser_params['reporting_person'] ?? NULL;
        $subuser_resource['report_to_data']['email'] = $subuser_params['reporting_person_email'] ?? NULL;
        $subuser_resource['report_to_data']['mobile_number'] = $subuser_params['reporting_person_mobile'] ?? NULL;
        $subuser_resource['report_to_data']['phone_number'] = $subuser_params['reporting_person_phone'] ?? NULL;


        $response = null;
        if (!empty($subuser_resource)) {
            $response = new SubuserAttributesResource($subuser_resource);
        }

        return $response;
    }

    /**
     * @param $operation
     * @param $subuser_params
     * @return mixed
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    private function updateOrCreateSubUserAttribute($operation, $subuser_params)
    {
        $subuser_attribute_data['current_assigned_cases'] = $subuser_params['collections']['current_assigned_cases'] ?? NULL;
        $subuser_attribute_data['untouched_cases'] = $subuser_params['collections']['untouched_cases'] ?? NULL;
        $subuser_attribute_data['cycle_target'] = $subuser_params['collections']['cycle_target'] ?? NULL;
        $subuser_attribute_data['current_paid_cases'] = $subuser_params['collections']['current_paid_cases'] ?? NULL;
        $subuser_attribute_data['to_be_collected_cases'] = $subuser_params['collections']['to_be_collected_cases'] ?? NULL;
        $subuser_attribute_data['to_be_achieved_cases'] = $subuser_params['collections']['to_be_achieved_cases'] ?? NULL;
        $subuser_attribute_data['due_date'] = $subuser_params['collections']['due_date'] ?? NULL;
        $subuser_attribute_data['allocation_date'] = $subuser_params['collections']['allocation_date'] ?? NULL;
        $subuser_attribute_data['days_in_cycle'] = $subuser_params['collections']['days_in_cycle'] ?? NULL;
        $subuser_attribute_data['days_left_in_cycle'] = $subuser_params['collections']['days_left_in_cycle'] ?? NULL;
        $subuser_attribute_data['drr'] = $subuser_params['collections']['drr'] ?? '';
        $subuser_attribute_data['paid_count_today'] = $subuser_params['collections']['paid_count_today'] ?? NULL;
        $subuser_attribute_data['ptp_count_today'] = $subuser_params['collections']['ptp_count_today'] ?? NULL;

        if ($operation == 'update') {
            $where_clause = ['subuser_reference_number' => $subuser_params['subuser_reference_number']];
            $subuser_attribute = $this->subuser_attributes_repository->update($subuser_attribute_data, $where_clause);
        } else {
            $subuser_attribute_data['subuser_reference_number'] = $subuser_params['subuser_reference_number'];
            $subuser_attribute = $this->subuser_attributes_repository->create($subuser_attribute_data);
        }

        return $subuser_attribute;
    }

    /**
     * Gets all subusers.
     * @return void
     * @throws ServiceCallFailedException
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function getAllSubusers()
    {
        $subuser_list = $this->repository->getSubuserList(); 
        if ($subuser_list->count() > 0) {
            foreach ($subuser_list as $data) { 
//                foreach ($value as $data) {
                AppLog::debug($this->class_name, 'getAllSubusers', 'Sync Subusers Request', [$data->subuser_reference_number]);
                $response = CommunicationServiceCall::subuserSync($data->subuser_reference_number);
                AppLog::debug($this->class_name, 'getAllSubusers', 'Sync Subusers Response', [$response]);
//                }
            }
        }
    }

    /**
     * Gets all subusers ratings.
     * @throws ServiceCallFailedException
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function getSubuserRatingDetails(string $call_reference_id)
    {
        return $this->subuser_ratings_repository->findByReferenceWithCondition($call_reference_id);
    }

    /**
     * add subusers ratings.
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function addSubuserRatings(array $data, $force = false)
    {   
        if ($force) {
            $call_entry = $this->subuser_ratings_repository->findByReference($data['call_reference_id']);
            if (!$call_entry) {
                $this->subuser_ratings_repository->create($data);
                return 1;
            } else {
                if ($call_entry->user_reference_number == "" || $call_entry->user_reference_number == null) {
                    $this->subuser_ratings_repository->update($data);
                }
                return 0;
            }
        }

        $call_entry = $this->subuser_ratings_repository->findByReferenceWithCondition($data['call_reference_id']);
        if ($call_entry) {
            $data['rating_created_at'] = Carbon::now();
            return $this->subuser_ratings_repository->update($data);
        } else {
            return 0;
        }
    }

    public function addQcRatings(array $data)
    {
        $call_entry = $this->subuser_ratings_repository->findByReference($data['call_reference_id']);
        $data['qc_rating_created_at'] = Carbon::now();
        $data['qc_by_subuser_reference_number'] = $this->repository->findBySubuserId($data['qc_by_subuser_id'])->subuser_reference_number;
        unset($data['qc_by_subuser_id']);
        
        if (!$call_entry) {
            $this->subuser_ratings_repository->create($data);    
            return 1;
        } else {
            if ($call_entry->qc_rating == "" || $call_entry->qc_rating == null) {
                return $this->subuser_ratings_repository->update($data);
            }
            return 0;
        }
    }


    /**
     * Gets all subusers roles.
     * @throws ServiceCallFailedException
     * @author Rikhil Jain <rikhil.jain@kissht.com>
     */
    public function getSubuserRoles(string $role_id)
    {
        $response = new SubuserRoleResource($this->subuser_roles_repository->findByReference($role_id));
        return $response;
    }

    /**
     * Get Subusers by name
     * @param array $data
     * @return object
     * @author Rikhil Jain <rikhil.jain@kissht.com>
     */
    public function getSubusersByName(array $data)
    {
        $final_response = null;
        $subuser_response = $this->repository->findByName($data['name']);
        if($subuser_response->isNotEmpty())
        {
            $final_response = SubuserResource::collection($subuser_response);
        }
        return $final_response;
    }

    /**
     * create role
     * @param array $data
     * @return object
     * @author Rikhil Jain <rikhil.jain@kissht.com>
     */
    public function createRole(array $data)
    {
        $subuser_roles_response = $this->subuser_roles_repository->create($data);
        return $subuser_roles_response;
    }

    /**
     * update role
     * @param array $data
     * @return object
     * @author Rikhil Jain <rikhil.jain@kissht.com>
     */
    public function updateRole(array $data)
    {
        $subuser_roles_response = $this->subuser_roles_repository->update($data,['role_id' => $data['role_id']]);
        return $subuser_roles_response;
    }

    /**
     * Get Subusers by subuser_reference_number
     * @param string $subuser_reference_number
     * @return object
     * @author Rikhil Jain <rikhil.jain@kissht.com>
     */
    public function getSubusersByReferenceNumber(string $subuser_reference_number)
    {
        $final_response = null;
        $subuser_response = $this->repository->findBySubuserRefNo($subuser_reference_number);
        if(!empty($subuser_response))
        {
            $final_response = new SubuserResource($subuser_response);
        }
        return $final_response;
    }

    /**
     * Gets all roles.
     * @throws ServiceCallFailedException
     * @author Rikhil Jain <rikhil.jain@kissht.com>
     */
    public function getAllRoles()
    {
        $response = RoleResource::collection($this->subuser_roles_repository->getAllRoles());
        return $response;
    }

    /**
     * @param $request_data
     * @return mixed
     * @author Yogesh Vishwakarma <yogesh.vishwakarma@kissht.com>
     */
    public function getBulkSubuserByField($request_data)
    {
        try {
            $entity = $this->repository->getBulkSubuserByField($request_data['subuser_reference_number']);

            $response = self::getResponseCode(1);
            if (!isset($entity)) {
                $response['data'][$this->entity] = [];
            } else {
                $response = $entity;

            }
        } catch (Exception $e) {
            $response = self::getResponseCode(201);
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    public function addDispositionRatings(array $data)
    {
        $rating_entry = $this->subuser_ratings_repository->findByReference($data['call_reference_id']);
        if (!$rating_entry) {
            $this->subuser_ratings_repository->create($data);
            return 1;
        } else {
            return 0;
        }
    }
}
