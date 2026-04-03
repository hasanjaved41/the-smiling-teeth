<?php

namespace App\Repositories;


/**
 * Class BaseRepository
 * @author Anil Chatla <anil.chatla@kissht.com>
 * @since 1.0.0
 */
class BaseRepository
{

    public function findByReference($referenceNumber)
    {
        return $this->model->find($referenceNumber);
    }

    public function create($params)
    {
        return $this->model->create($params);
    }

    public function update($params, $referenceNumber)
    {
        $entity = $this->model->findOrFail($referenceNumber);
        $entity->update($params);

        return $entity;
    }

}

