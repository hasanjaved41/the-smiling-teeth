<?php

namespace App\Repositories;

use App\Models\AuthKey;
use Illuminate\Support\Facades\Cache;

/**
 * Class AuthRepository
 * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
 * @since 1.0.0
 */
class AuthKeyRepository
{

    private $model;

    public function __construct(AuthKey $authKeyModel)
    {
        $this->model = $authKeyModel;
    }

    public function getClientIdDetails($key_id)
    {
        return Cache::tags('auth')->rememberForever($key_id, function () use ($key_id) {
            $auth_credentials_details = $this->model->select('key_secret', 'source_reference_number','source_type')->where('key_id',
                $key_id)->first();
            if (!empty($auth_credentials_details)) {
                $auth_client_details = $auth_credentials_details->authClient;
                $auth_credentials_details['auth_client'] = $auth_client_details;
            }
            return $auth_credentials_details;
        });
    }
}

