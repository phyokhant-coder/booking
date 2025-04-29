<?php

namespace App\Repositories\ProductImage;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Builder;

class ProductImageRepository extends BaseRepository implements ProductImageRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhereHas('product', function ($q) use ($options) {
                    $q->where('name', 'like', "%{$options['search']}%");
                });
            });
        }

        return $query;
    }

    public function connection(): ProductImage
    {
        return new ProductImage;
    }
}
