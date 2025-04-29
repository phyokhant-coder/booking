<?php

namespace App\Repositories\State;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\State;
use Illuminate\Database\Eloquent\Builder;

class StateRepository extends BaseRepository implements StateRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('name', 'like', "%{$options['search']}%")
                    ->orWhereHas('country', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%");
                    });
            });
        }

        return $query;
    }

    public function connection(): State
    {
        return new State;
    }
}
