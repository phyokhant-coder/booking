<?php

namespace App\Repositories\Permission;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function getAll(array $options = [])
    {
        return $this->optionsQuery($options)->get();
    }

    protected function optionsQuery(array $options): Builder
    {
        $query = $this->connection()->query();

        if (isset($options['guard_name'])) {
            $query = $query->where('guard_name', $options['guard_name']);
        }

        if (isset($options['limit'])) {
            $query = $query->limit($options['limit']);
        }

        if (isset($options['offset'])) {
            $query = $query->offset($options['offset']);
        }

        if (!empty($options['search'])) {
            $query = $query->where('name', 'like' , "%{$options['search']}%");
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function totalCount(array $options = []): int
    {
        return $this->optionsQuery($options)->count();
    }

    protected function connection(): Permission
    {
        return new Permission();
    }
}
