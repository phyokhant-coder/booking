<?php

namespace App\Repositories\Product;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query->orWhere('name', 'like', "%{$options['search']}%")
                ->orWhere('product_code', 'like', "%{$options['search']}%")
                ->orWhere('description', 'like', "%{$options['search']}%")
                ->orWhere('price', 'like', "%{$options['search']}%")
                ->orWhereHas('productBrand', function ($query) use ($options) {
                    $query->where('name', 'like', "%{$options['search']}%");
                })
                ->orWhereHas('productCategory', function ($query) use ($options) {
                    $query->where('name', 'like', "%{$options['search']}%");
                });
        }

        if (isset($options['sort'])) {
            $query->orderBy($options['sort'], $options['order']);
        }

        if (isset($options['product_category_id'])) {
            $query->where('product_category_id', $options['product_category_id']);
        }

        if (isset($options['product_brand_id'])) {
            $query->where('product_brand_id', $options['product_brand_id']);
        }

        if (isset($options['product_ids'])) {
            $query->whereIn('id',$options['product_ids']);
        }

        return $query;
    }

    public function connection(): Product
    {
        return new Product;
    }
}
