<?php

namespace App\Repositories\Size;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;

class SizeRepository extends BaseRepository implements SizeRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('name', 'like', "%{$options['search']}%")
                    ->orWhere('code', 'like', "%{$options['search']}");
            });
        }

        return $query;
    }

    public function connection(): Size
    {
        return new Size;
    }
}
