<?php

namespace App\Repositories\Color;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Color;
use Illuminate\Database\Eloquent\Builder;

class ColorRepository extends BaseRepository implements ColorRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('title', 'like', "%{$options['search']}%")
                    ->orWhere('description', 'like', "%{$options['search']}");
            });
        }

        return $query;
    }

    public function connection(): Color
    {
        return new Color;
    }
}
