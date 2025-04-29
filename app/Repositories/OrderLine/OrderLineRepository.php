<?php

namespace App\Repositories\OrderLine;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\OrderLine;
use Illuminate\Database\Eloquent\Builder;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('order_line_no', 'like', "%{$options['search']}%")
                    ->orWhere('price', 'like', "%{$options['search']}%")
                    ->orWhere('quantity', 'like', "%{$options['search']}%")
                    ->orWhere('total_price', 'like', "%{$options['search']}%")
                    ->orWhereHas('order', function ($query) use ($options) {
                        $query->where('order_no', 'like', "%{$options['search']}%");
                    })
                    ->orWhereHas('product', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%");
                    })
                    ->orWhereHas('productVariant', function ($query) use ($options) {
                        $query->where('color', 'like', "%{$options['search']}%");
                    });

            });
        }

        return $query;
    }

    public function connection(): OrderLine
    {
        return new OrderLine;
    }
}
