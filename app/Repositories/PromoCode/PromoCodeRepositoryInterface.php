<?php

namespace App\Repositories\PromoCode;

use App\Api\Foundation\Repository\EloquentRepositoryInterface;

interface PromoCodeRepositoryInterface extends EloquentRepositoryInterface
{
    public function getDataByCode($code, array $relations = []);
}
