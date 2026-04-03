<?php

namespace App\Repositories;

use App\Models\Subuser;
use App\Models\UnifiedComments;
use App\Repositories\Interfaces\RepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PhpParser\Node\Expr\Cast\Object_;

/**
 * Class SubuserRepository
 * @package App\Repositories
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
class UnifiedCommentsRepository
{
    /**
     * Instance of Unified Comments Model
     *
     * @var \App\Models\Subuser
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    protected $model;

    public function __construct(UnifiedComments $unified_comments)
    {
        $this->model = $unified_comments;
    }

    /**
     * Get unified comments using user_reference_number
     * @param array $data
     * @return Object_
     * @throws ModelNotFoundException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getUnifiedCommentByCondition($data)
    {
        $user_reference_number = $data['user_reference_number'] ?? null;
        $action_refrence_number = $data['source_reference_number'] ?? null;
        $source = $data['source'] ?? null;

        return $this->model
            ->where('user_reference_number', $user_reference_number)
            ->where('action_reference_number', $action_refrence_number)
            ->where('action_type', $source)
            ->get();
    }

    /**
     * Get latest unified comments using user_reference_number
     * @param array $data
     * @return Object_
     * @throws ModelNotFoundException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function getLatestUnifiedCommentByCondition($data)
    {
        $user_reference_number = $data['user_reference_number'] ?? null;
        $action_reference_number = $data['source_reference_number'] ?? null;
        $source = $data['source'] ?? null;
        $disposition = $data['disposition'] ?? null;

        $latest_comment = $this->model
            ->where('user_reference_number', $user_reference_number)
            ->where('action_reference_number', $action_reference_number)
            ->where('action_type', $source);

        if ($disposition != null) {
            $latest_comment = $latest_comment->where('disposition', $disposition);
        }

        $latest_comment = $latest_comment->orderBy('created_at','desc')->first();

        return $latest_comment;
    }

    /**
     * Get unified comments using user_reference_number
     * @param array $data
     * @return Object_
     * @throws ModelNotFoundException
     * @author Rohan Parkar <rohan.parkar@kissht.com>
     */
    public function getBulkUnifiedCommentByCondition($param)
    {
        $user_reference_number = $param['user_reference_number'] ?? null;
        $action_reference_number = $param['source_reference_number'] ?? null;
        $action_type = $param['source'] ?? null;
        $disposition = $param['disposition'] ?? null;

        return $this->model::query()
            ->when($action_reference_number, function ($query, $action_reference_number) {
                return $query->whereIn('action_reference_number', explode(",",$action_reference_number));
            })
            ->when($action_type, function ($query, $action_type) {
                return $query->where('action_type', $action_type);
            })
            ->when($user_reference_number, function ($query, $user_reference_number) {
                return $query->whereIn('user_reference_number', explode(",",$user_reference_number));
            })
            ->when($disposition, function ($query, $disposition) {
                return $query->where('disposition', $disposition);
            })
            ->groupBy('user_reference_number')
            ->orderBy('created_at', 'desc')
            ->get();
    }

}