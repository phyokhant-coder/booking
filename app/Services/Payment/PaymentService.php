<?php

namespace App\Services\Payment;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\PaymentResource;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentService extends CommonService
{
    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));
        $params['with'] = [
            'paymentMethod'
        ];
        $payments = $this->paymentRepository->all($params);

        if ($payments->isNotEmpty()) {
            $data['data'] = PaymentResource::collection($payments)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->paymentRepository->totalCount($params);
        }

        return $data;
    }

    public function createPayment($request): array
    {
        $input = $this->input($request, $this->paymentRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'payments']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);
        

            $payment = $this->paymentRepository->insert($input);

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
    public function getPaymentById($id): mixed
    {
        $payment = $this->paymentRepository->getDataById($id);

        if(!$payment) {
            throw new NotFoundResourceException();
        }

        return $payment;
    }

    /**
     * @param $request
     * @param $id
     * @param $role
     * @return array
     * @throws Exception
     */
    public function updatePayment($request, $id): array
    {
        $input = $this->input($request, $this->paymentRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'payments']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->paymentRepository->update($input, $id);

            $payment = $this->getPaymentById($id);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new PaymentResource($payment))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deletePayment($id): array
    {
        $payment = $this->getPaymentById($id);

        try {
            DB::beginTransaction();

            $this->paymentRepository->destroy($payment->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
