<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\ProductBrand\StoreProductBrandRequest;
use App\Services\ProductBrand\ProductBrandService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    protected ProductBrandService $service;

    public function __construct(ProductBrandService $service)
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
        if (!auth()->user()->can('view-product-brands')) {
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
     * @param StoreProductBrandRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductBrandRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-product-brands')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createProductBrand($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreProductBrandRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreProductBrandRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-product-brands')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateProductBrand($request, $id);

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
        if (!auth()->user()->can('delete-product-brands')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteProductBrand($id);

        return $this->response((array)$response);
    }

    /**
     * Brand Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function brandList(Request $request): JsonResponse
    {
        $response = $this->service->getAll($request);

        return $this->response($response['data'], $response['count'], true);
    }
}
