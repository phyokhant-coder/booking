<?php

namespace App\Repositories\Payment;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhereHas('order', function ($query) use ($options) {
                        $query->where('order_no', 'like', "%{$options['search']}%");
                    })
                    ->orWhereHas('paymentMethod', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%");
                    });
            });
        }

        return $query;
    }

    public function connection(): Payment
    {
        return new Payment;
    }
}
