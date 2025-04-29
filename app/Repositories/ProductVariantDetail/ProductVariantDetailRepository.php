<?php

namespace App\Repositories\ProductVariantDetail;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\ProductVariantDetail;
use Illuminate\Database\Eloquent\Builder;

class ProductVariantDetailRepository extends BaseRepository implements ProductVariantDetailRepositoryInterface
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

    public function connection(): ProductVariantDetail
    {
        return new ProductVariantDetail;
    }
}
