<?php

namespace App\Repositories\BillingAddress;

use App\Api\Foundation\Repository\Eloquent\BaseRepository;
use App\Models\BillingAddress;
use Illuminate\Database\Eloquent\Builder;

class BillingAddressRepository extends BaseRepository implements BillingAddressRepositoryInterface
{
    public function optionsQuery(array $options): Builder
    {
        $query = parent::optionsQuery($options);

        if (isset($options['search'])) {
            $query = $query->where(function ($query) use ($options) {
                $query->orWhere('first_name', 'like', "%{$options['search']}%")
                    ->orWhere('last_name', 'like', "%{$options['search']}%")
                    ->orWhere('email', 'like', "%{$options['search']}%")
                    ->orWhere('phone_number', 'like', "%{$options['search']}%")
                    ->orWhere('full_address', 'like', "%{$options['search']}%")
                    ->orWhere('remark', 'like', "%{$options['search']}%")
                    ->orWhereHas('country', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%");
                    })
                    ->orWhereHas('state', function ($query) use ($options) {
                        $query->where('name', 'like', "%{$options['search']}%");
                    });

            });
        }

        if (isset($options['user_id'])) {
            $query = $query->where('user_id', $options['user_id']);
        }

        return $query;
    }

    public function connection(): BillingAddress
    {
        return new BillingAddress;
    }
}
