<?php

namespace App\Repositories\PaymentMethod;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder;

class PaymentMethodRepository extends BaseRepository implements PaymentMethodRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('name', 'like', "%{$options['search']}%")
                    ->orWhere('account_name', 'like', "%{$options['search']}%")
                    ->orWhere('account_number', 'like', "%{$options['search']}%");
            });
        }

        return $query;
    }

    public function connection(): PaymentMethod
    {
        return new PaymentMethod;
    }
}
