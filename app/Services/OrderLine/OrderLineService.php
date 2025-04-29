<?php

namespace App\Services\OrderLine;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\OrderLineResource;
use App\Repositories\OrderLine\OrderLineRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderLineService extends CommonService
{
    protected OrderLineRepositoryInterface $orderLineRepository;

    public function __construct(OrderLineRepositoryInterface $orderLineRepository)
    {
        $this->orderLineRepository = $orderLineRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $orderLines = $this->orderLineRepository->all($params);

        if ($orderLines->isNotEmpty()) {
            $data['data'] = OrderLineResource::collection($orderLines)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->orderLineRepository->totalCount($params);
        }

        return $data;
    }

    public function createOrderLine(Request $request): array
    {
        try {
            // Start a database transaction
            DB::beginTransaction();

            // Array to store the results of each order line processing
            $results = [];

            // Validate the input data
            $orderLines = $request->all(); // Get the raw array from the request
            if (empty($orderLines) || !is_array($orderLines)) {
                throw new Exception('Invalid or missing order lines data.');
            }

            // Process each order line
            foreach ($orderLines as $orderLineRequest) {
                try {
                    if (!is_array($orderLineRequest)) {
                        throw new Exception('Invalid order line format. Each order line should be an array.');
                    }

                    $orderLineRequestInstance = new Request($orderLineRequest);
                    $input = $this->input($orderLineRequestInstance, $this->orderLineRepository->connection()->getFillable());
                    $prepareInput = $this->prepareInput($orderLineRequestInstance);
                    $prepareInput['order_line_no'] = $this->generateOrderLineNumber();

                    // Merge prepared data with extracted input
                    $orderLineData = array_merge($input, $prepareInput);

                    // Insert the order line into the database
                    $this->orderLineRepository->insert($orderLineData);

                    // Record success for this order line
                    $results[] = [
                        'order_line' => $orderLineRequest,
                        'success' => true,
                    ];
                } catch (Exception $e) {
                    // Handle errors for the current order line
                    $results[] = [
                        'order_line' => $orderLineRequest,
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            // Commit the transaction if all order lines are processed
            DB::commit();

            // Return the detailed results for each order line
            return [
                'stored' => true,
                'results' => $results,
            ];
        } catch (Exception $e) {
            // Roll back the transaction in case of any failure
            DB::rollBack();

            // Return an error response
            return [
                'stored' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getOrderLineById($id): mixed
    {
        $orderLine = $this->orderLineRepository->getDataById($id);

        if(!$orderLine) {
            throw new NotFoundResourceException();
        }

        return $orderLine;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateOrderLine($request, $id): array
    {
        $input = $this->input($request, $this->orderLineRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->orderLineRepository->update($input, $id);

            $orderLine = $this->getOrderLineById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new OrderLineResource($orderLine))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteOrderLine($id): array
    {
        $orderLine = $this->getOrderLineById($id);

        try {
            DB::beginTransaction();

            $this->orderLineRepository->destroy($orderLine->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }

    private function generateOrderLineNumber(): string
    {
        return 'ORDL-' . strtoupper(uniqid());
    }
}
