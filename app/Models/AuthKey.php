<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Auth
 * @author Mukesh Kurmi <mukesh.kurmi@kissht.com>
 * @since 1.0.0
 */
class AuthKey extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_fastbanking_auth_keys';

    /**
     * Get the client record associated with the auth_key.
     */
    public function authClient()
    {
        return $this->hasOne('App\Models\AuthClient','auth_client_reference_number','source_reference_number');
    }
}
