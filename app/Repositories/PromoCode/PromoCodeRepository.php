<?php

namespace App\Repositories\PromoCode;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Builder;

class PromoCodeRepository extends BaseRepository implements PromoCodeRepositoryInterface
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

    public function getDataByCode($code, array $relations = [])
    {
        return $this->connection()->query()->with($relations)->where('code', $code)->first();
    }

    public function connection(): PromoCode
    {
        return new PromoCode;
    }
}
