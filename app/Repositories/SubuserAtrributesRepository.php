<?php

namespace App\Repositories;

use App\Models\SubuserAttributes;

class SubuserAtrributesRepository {

    protected $model;

    public function __construct(SubuserAttributes $subuser_attributes)
    {
        $this->model = $subuser_attributes;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($params, $where_clause)
    {
        $entity = $this->model->where($where_clause)->first();
        if (!empty($entity)) {
            $entity->update($params);
        }

        return $entity;
    }

    public function findByField($field_name, $field_value, $multiple = false)
    {
        if ($multiple) {
            return $this->model->where($field_name, $field_value)->get();
        } else {
            return $this->model->where($field_name, $field_value)->first();
        }
    }
}
