<?php

namespace App\Repositories\ProductCategory;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;

class ProductCategoryRepository extends BaseRepository implements ProductCategoryRepositoryInterface
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

    public function connection(): ProductCategory
    {
        return new ProductCategory;
    }
}
