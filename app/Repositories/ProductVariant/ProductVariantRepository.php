<?php

namespace App\Repositories\ProductVariant;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;

class ProductVariantRepository extends BaseRepository implements ProductVariantRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('color', 'like', "%{$options['search']}%")
                    ->orWhereHas('product', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%");
                    });
            });
        }

        if (($options['add_stock'] ?? false) === true) {
            $query = $query->increment('stock', $options['stock']);
        }

        return $query;
    }

    public function connection(): ProductVariant
    {
        return new ProductVariant;
    }
}
