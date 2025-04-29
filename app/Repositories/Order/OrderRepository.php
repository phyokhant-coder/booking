<?php

namespace App\Repositories\Order;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);
        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('order_no', 'like', "%{$options['search']}%")
                    ->orWhere('total_amount', 'like', "%{$options['search']}%")
                    ->orWhere('status', 'like', "%{$options['search']}%")
                    ->orWhere('order_note', 'like', "%{$options['search']}%")
                    ->orWhereDate('order_date', '=', $options['search'])
                    ->orWhereHas('user', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%")
                            ->orWhere('email', 'like', "%{$options['search']}%")
                            ->orWhere('phone_number', 'like', "%{$options['search']}%");
                    })
                    ->orWhereHas('billingAddress', function ($query) use ($options) {
                        $query->where('full_address', 'like', "%{$options['search']}%");
                    });
            });

            
        }
        if (isset($options['status'])) {
            // dd(true);
            $query->where('status', $options['status']);
        }

        if (isset($options['user_id'])) {
            $query->where('user_id', $options['user_id']);
        }
        // dd($query->toSql());
        return $query;
    }

    public function connection(): Order
    {
        return new Order;
    }
}
