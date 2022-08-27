<?php

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Models\UserLoginHistory;
use App\Repository\UserRepositoryInterface;

class HistoriesRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(UserLoginHistory $model)
    {
        $this->model = $model;
    }
}
