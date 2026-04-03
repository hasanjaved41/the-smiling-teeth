<?php
namespace App\Services;

use App\Exceptions\ValidationFailedException;
use App\Helpers\AppLogHelper as AppLog;
use App\Repositories\UnifiedCommentsRepository;
use App\Services\Traits\ResponseCodeTrait;

/**
 * Class UserService
 * @package App\Services
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class UserService
{
    use ResponseCodeTrait;

    protected $unified_comments_repository;

    private $class_name = 'UserService';

    public function __construct(UnifiedCommentsRepository $unified_comments_repository)
    {
        $this->unified_comments_repository = $unified_comments_repository;
    }
    /**
     * Validation rules
     * @param array $required_fields
     * @return array $rules
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     *
     */
    public function getRules(array $required_fields = [])
    {
        $rules = array();
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
     * Get users unified comments data
     * @param array $data
     * @return mixed
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getUserUnifiedComments($data)
    {   
        $unified_comments_response = $this->unified_comments_repository->getUnifiedCommentByCondition($data);

        return $unified_comments_response;
    }

    /**
     * Get users latest unified comments data
     * @param array $data
     * @return mixed
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getLatestUserUnifiedComments($data)
    {
        $unified_comments_response = $this->unified_comments_repository->getLatestUnifiedCommentByCondition($data);

        return $unified_comments_response;
    }

    /**
     * Get bulk users unified comments data
     * @param array $data
     * @return mixed
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function getBulkUnifiedComments($param)
    {
        $unified_comments_response = $this->unified_comments_repository->getBulkUnifiedCommentByCondition($param);
        
        return $unified_comments_response;   
    }
}