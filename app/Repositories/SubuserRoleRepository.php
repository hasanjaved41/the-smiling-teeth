<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class SubuserRoleRepository
 * @package App\Repositories
 * @author Rikhil Jain <rikhil.jainle@kissht.com>
 * @since 1.0.0
 */
class SubuserRoleRepository
{
    /**
     * Instance of Role Model
     *
     * @var \App\Models\Role
     * @author Rikhil Jain <rikhil.jainle@kissht.com>
     */
    protected $model;

    public function __construct(Role $roles)
    {
        $this->model = $roles;
    }

    public function create(array $params)
    {
        return $this->model->create($params);
    }

    public function update(array $params, array $where_clause)
    {
        $entity = $this->model->where($where_clause)->first();
        if (!empty($entity)) {
            return $entity->update($params);
        }
    }

    public function findByReference(string $role_id)
    {
        return $this->model->where('role_id', $role_id)->first();
    }

    public function getAllRoles()
    {
        return $this->model->get();
    }

}
