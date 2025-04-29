<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\Order\StoreGuestUserOrderRequest;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\Order\OrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    /**
     * Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('view-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->getAll($request);

        return $this->response($response['data'], $response['count'], true);
    }

    /**
     * store the requested data to db
     *
     * @param StoreOrderRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('create-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createOrder($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * @throws NotFoundResourceException
     */
    public function show($id): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('view-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $order = $this->service->getOrderById($id, [
            'user',
            'billingAddress',
            'orderLines.productVariant',
            'orderLines.productVariantDetail',
            'orderLines.product.productCategory',
            'orderLines.product.productBrand',
        ]);

        return response()->json(new OrderResource($order));
    }

    /**
     * update the requested data to db
     *
     * @param StoreOrderRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreOrderRequest $request, $id): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('update-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateOrder($request, $id);

        return $this->response($response);
    }

    /**
     * Delete the requested data from db
     *
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     */
    public function destroy($id): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('delete-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteOrder($id);

        return $this->response((array)$response);
    }

    /**
     * @throws Exception
     */
    public function orderChangeStatus(Request $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateOrderChangeStatus($request, $id);

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreGuestUserOrderRequest $request
     * @return JsonResponse
     * @throws Exception
     */
    public function storeGuestUserOrder(StoreGuestUserOrderRequest $request): JsonResponse
    {
        // if (!auth()->user()->can('update-orders')) {
        //     return response()->json([
        //         'error' => 'You don\'t have permission.'
        //     ], 403);
        // }
        $response = $this->service->storeGuestUserOrder($request);

        return $this->response($response);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function pendingOrderCount(): JsonResponse
    {
        $response = $this->service->pendingOrderCount();

        return $this->response($response);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function confirmOrderCount(): JsonResponse
    {
        $response = $this->service->confirmOrderCount();

        return $this->response($response);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function cancelOrderCount(): JsonResponse
    {
        $response = $this->service->cancelOrderCount();

        return $this->response($response);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function shippedOrderCount(): JsonResponse
    {
        $response = $this->service->shippedOrderCount();

        return $this->response($response);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function deliveredOrderCount(): JsonResponse
    {
        $response = $this->service->deliveredOrderCount();

        return $this->response($response);
    }

    /**
     * Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function monthlyBestSellingProductList(Request $request): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('view-orders')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->monthlyBestSellingProductList($request);

        return $this->response($response['data'], $response['count'], true);
    }

    /**
     * * @param Request $request
     * @throws NotFoundResourceException
     */
    public function orderDetail(Request $request): JsonResponse
    {
        $orderDetails = $this->service->getOrderDetailByUserId($request);

        return $this->response($orderDetails['data'], $orderDetails['count'], true);
    }
    
}
