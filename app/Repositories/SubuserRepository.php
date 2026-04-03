<?php

namespace App\Repositories;

use App\Models\Subuser;
use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PhpParser\Node\Expr\Cast\Object_;

/**
 * Class SubuserRepository
 * @package App\Repositories
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class SubuserRepository implements RepositoryInterface
{
    /**
     * Instance of Sub user Model
     *
     * @var \App\Models\Subuser
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    protected $model;

    public function __construct(Subuser $subuser)
    {
        $this->model = $subuser;
    }

    /**
     * get details by email.
     * @param string $email
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();

    }

    /**
     * Get subuser Data using subuser_reference_number
     * @param string $subuser_reference_number
     * @return Object
     * @throws ModelNotFoundException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function findByReference(string $subuser_reference_number)
    {
        return $this->model->where('subuser_reference_number', $subuser_reference_number)->firstOrFail();
    }

    /**
     * Create
     * @param array $data
     * @return Object
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * update resource
     * @param array $params
     * @param string $reference_number
     * @return array
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function update(string $reference_number, array $params)
    {
        $entity = $this->model->where('subuser_reference_number', $reference_number)->first();

        if (!empty($entity)) {
            return $entity->update($params);
        }
    }

    public function delete($id)
    {
        //We are not implementing this function for the userRepository
        return false;
    }

    public function show($id)
    {
        return $this->subuser->findOrFail($id);
    }

    /**
     * Get subuser Data using subuser_reference_number
     * @param string $subuser_reference_number
     * @return Object_
     * @throws ModelNotFoundException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function findOrFailByReference(string $subuser_reference_number)
    {
        return $this->model->where('subuser_reference_number', $subuser_reference_number)->firstOrFail();
    }

    /**
     * Get email by imei number
     * @param string $imei_number
     * @param string $field
     * @return Object_
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function checkEmailWithImei(string $field, string $imei_number)
    {
        if ($field == 'imei_number') {
            return $this->model->where('imei_number', $imei_number)->first();
        } else if ($field == 'advertising_id') {
            return $this->model->where('advertising_id', $imei_number)->first();
        }
    }


    /**
     * Get subuser Data using subuser_reference_number
     * @param array $condition
     * @param array $limit , $offset
     * @return Object_
     * @throws ModelNotFoundException
     * @author Neha G <neha.gupta@kissht.com>
     */
    public function getSubuserByCondition($condition = [], $offset, $limit)
    {
        $role = (!empty($condition['role'])) ? $condition['role'] : null;
        $is_active = (isset($condition['is_active'])) ? $condition['is_active'] : null;

        $subuser_reference_numbers = (!empty($condition['subuser_reference_number'])) ? $condition['subuser_reference_number'] : null;

        return $this->model->query()
            ->when($role, function ($query, $role) {
                return $query->where('role', $role);
            })
            ->when($is_active, function ($query, $is_active) {
                return $query->where('is_active', $is_active);
            })
            ->when($subuser_reference_numbers, function ($query, $subuser_reference_numbers) {
                return $query->whereIn('subuser_reference_number', array_unique(explode(',', $subuser_reference_numbers)));
            })
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get reporting details by report_to id
     * @param array $report_to
     * @return Object_
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getReportToDetails($report_to)
    {
        return $this->model->where('subuser_id', $report_to)->first();
    }

    /**
     * Get subuser list
     * @return Object_
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function getSubuserList()
    {
        return $this->model->where('role', 11)->where('is_active', '1')->whereNotNull('subuser_reference_number')->get();
    }

    /**
     * Get subuser Id
     * @return Object_
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function findBySubuserId($subuser_id)
    {
        return $this->model->where('subuser_id', $subuser_id)->first();
    }

    /**
     * get details by name.
     * @param string $name
     * @return array
     * @author Rikihl Jain <rikhil.jain@kissht.com>
     */
    public function findByName(string $name)
    {
        return $this->model->with('roles')->where('name','LIKE','%'.$name.'%')->where('is_active',"1")->get();
    }

    /**
     * get details by subuser_reference_number.
     * @param string $subuser_reference_number
     * @return array
     * @author Rikihl Jain <rikhil.jain@kissht.com>
     */
    public function findBySubuserRefNo(string $subuser_reference_number)
    {
        return $this->model->with('roles')->where('subuser_reference_number',$subuser_reference_number)->first();
    }

    public function getBulkSubuserByField($subuser_reference_numbers)
    {
        return $this->model->whereIn('subuser_reference_number', $subuser_reference_numbers)->limit(500)->get();
    }
}
