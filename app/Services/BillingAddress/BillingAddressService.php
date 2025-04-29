<?php

namespace App\Services\BillingAddress;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\BillingAddressResource;
use App\Repositories\BillingAddress\BillingAddressRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingAddressService extends CommonService
{
    protected BillingAddressRepositoryInterface $billingAddressRepository;

    public function __construct(BillingAddressRepositoryInterface $billingAddressRepository)
    {
        $this->billingAddressRepository = $billingAddressRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));
        if (auth()->guard('api')->check()) {
            $params['user_id'] = auth()->guard('api')->id();
        }
        $params['with'] = [
            'user',
            'country',
            'state'
        ];

        $billingAddresses = $this->billingAddressRepository->all($params);
        // dd($billingAddresses);

        if ($billingAddresses->isNotEmpty()) {
            $data['data'] = BillingAddressResource::collection($billingAddresses)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->billingAddressRepository->totalCount($params);
        }

        return $data;
    }

    public function createBillingAddress($request): array
    {
        $input = $this->input($request, $this->billingAddressRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);
            if (auth()->guard('api')->check()) {
                $input['user_id'] = auth()->guard('api')->id();
            }

            $this->billingAddressRepository->insert($input);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return ['stored' => false, 'message' => $e->getMessage()];
        }

        return ['stored' => true];
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getBillingAddressById($id): mixed
    {
        $billingAddress = $this->billingAddressRepository->getDataById($id);

        if(!$billingAddress) {
            throw new NotFoundResourceException();
        }

        return $billingAddress;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateBillingAddress($request, $id): array
    {
        $input = $this->input($request, $this->billingAddressRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->billingAddressRepository->update($input, $id);

            $billingAddress = $this->getBillingAddressById($id);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new BillingAddressResource($billingAddress))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteBillingAddress($id): array
    {
        $billingAddress = $this->getBillingAddressById($id);

        try {
            DB::beginTransaction();

            $this->billingAddressRepository->destroy($billingAddress->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }


    /**
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getBillingAddressDetailByUserId($request): mixed
    {
        $data = ['data' => [], 'count' => 0];
        $params = $request->all();
        $params = array_merge($params, $this->getFilterParams($request));
        if (auth()->guard('api')->check()) {
            $params['user_id'] = auth()->guard('api')->id();
        }
        $params['with'] = [
            'user',
            'country',
            'state'
        ];

        $billingAddresses = $this->billingAddressRepository->all($params);
        // dd($billingAddresses);

        if ($billingAddresses->isNotEmpty()) {
            $data['data'] = BillingAddressResource::collection($billingAddresses)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->billingAddressRepository->totalCount($params);
        }

        return $data;
    }
}
