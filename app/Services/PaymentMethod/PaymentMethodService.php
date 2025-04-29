<?php

namespace App\Services\PaymentMethod;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\PaymentMethodResource;
use App\Repositories\PaymentMethod\PaymentMethodRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentMethodService extends CommonService
{
    protected PaymentMethodRepositoryInterface $paymentMethodRepository;

    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $paymentMethods = $this->paymentMethodRepository->all($params);

        if ($paymentMethods->isNotEmpty()) {
            $data['data'] = PaymentMethodResource::collection($paymentMethods)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->paymentMethodRepository->totalCount($params);
        }

        return $data;
    }

    public function createPaymentMethod($request): array
    {
        $input = $this->input($request, $this->paymentMethodRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'payment_methods']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->paymentMethodRepository->insert($input);

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
    public function getPaymentMethodById($id): mixed
    {
        $paymentMethod = $this->paymentMethodRepository->getDataById($id);

        if(!$paymentMethod) {
            throw new NotFoundResourceException();
        }

        return $paymentMethod;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updatePaymentMethod($request, $id): array
    {
        $input = $this->input($request, $this->paymentMethodRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'payment_methods']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->paymentMethodRepository->update($input, $id);

            $paymentMethod = $this->getPaymentMethodById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new PaymentMethodResource($paymentMethod))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deletePaymentMethod($id): array
    {
        $paymentMethod = $this->getPaymentMethodById($id);

        try {
            DB::beginTransaction();

            $this->paymentMethodRepository->destroy($paymentMethod->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
