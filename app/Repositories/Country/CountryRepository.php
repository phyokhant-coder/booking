<?php

namespace App\Repositories\Country;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Country;
use Illuminate\Database\Eloquent\Builder;

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('name', 'like', "%{$options['search']}%");
            });
        }

        return $query;
    }

    public function connection(): Country
    {
        return new Country;
    }
}
