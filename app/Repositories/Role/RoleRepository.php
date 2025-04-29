<?php

namespace App\Repositories\Role;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Api\Foundation\Repository\Eloquent\BaseRepository;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (!empty($options['guard_name'])) {
            $query = $query->where('guard_name', $options['guard_name']);
        }

        if (!empty($options['name'])) {
            $query = $query->where('name', $options['name']);
        }

        if (!empty($options['search'])) {
            $query = $query->orWhere('name', 'like', "%{$options['search']}%")
                ->orWhere('remark', 'like', "%{$options['search']}%");
        }

        return $query;
    }

    public function connection(): Model
    {
        return new Role();
    }
}
