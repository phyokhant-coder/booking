<?php

namespace App\Repositories\User;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('name', 'like', "%{$options['search']}%")
                    ->orWhere('email', 'like', "%{$options['search']}")
                    ->orWhere('phone_number', 'like', "%{$options['search']}");
            });
        }

        return $query;
    }

    public function connection(): User
    {
        return new User;
    }
}
