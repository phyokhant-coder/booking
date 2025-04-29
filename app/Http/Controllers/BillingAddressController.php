<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\BillingAddress\StoreBillingAddressRequest;
use App\Services\BillingAddress\BillingAddressService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingAddressController extends Controller
{
    protected BillingAddressService $service;

    public function __construct(BillingAddressService $service)
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
        if (auth()->guard('admin')->check() && !auth('admin')->user()->can('view-billing-addresses')) {
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
     * @param StoreBillingAddressRequest $request
     * @return JsonResponse
     * @throws NotFoundResourceException
     */
    public function store(StoreBillingAddressRequest $request): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth('admin')->user()->can('create-billing-addresses')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createBillingAddress($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreBillingAddressRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreBillingAddressRequest $request, $id): JsonResponse
    {
        if (auth()->guard('admin')->check() && !auth()->user()->can('update-billing-addresses')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateBillingAddress($request, $id);

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
        if (auth()->guard('admin')->check() && !auth()->user()->can('delete-billing-addresses')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteBillingAddress($id);

        return $this->response((array)$response);
    }

    /**
     * * @param Request $request
     * @throws NotFoundResourceException
     */
    public function userBillingAddressDetail(Request $request): JsonResponse
    {
        $billingAddressDetails = $this->service->getBillingAddressDetailByUserId($request);

        return $this->response($billingAddressDetails['data'], $billingAddressDetails['count'], true);
    }
}
