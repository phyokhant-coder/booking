<?php

namespace App\Repositories\Cart;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Builder;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['user_id'])) {
            $query = $query->where('user_id', $options['user_id']);
        }

        return $query;
    }

    public function connection(): Cart
    {
        return new Cart;
    }
}
