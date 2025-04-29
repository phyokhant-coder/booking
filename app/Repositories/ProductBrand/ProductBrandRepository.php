<?php

namespace App\Repositories\ProductBrand;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\ProductBrand;
use Illuminate\Database\Eloquent\Builder;

class ProductBrandRepository extends BaseRepository implements ProductBrandRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('name', 'like', "%{$options['search']}%")
                    ->orWhere('description', 'like', "%{$options['search']}");
            });
        }

        return $query;
    }

    public function connection(): ProductBrand
    {
        return new ProductBrand;
    }
}
