<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\ProductCategory\StoreProductCategoryRequest;
use App\Services\ProductCategory\ProductCategoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    protected ProductCategoryService $service;

    public function __construct(ProductCategoryService $service)
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
        if (!auth()->user()->can('view-product-categories')) {
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
     * @param StoreProductCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-product-categories')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }
        $response = $this->service->createProductCategory($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreProductCategoryRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreProductCategoryRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-product-categories')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateProductCategory($request, $id);

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
        if (!auth()->user()->can('delete-product-categories')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteProductCategory($id);

        return $this->response((array)$response);
    }

    /**
     * Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryList(Request $request): JsonResponse
    {
        // if (!auth()->user()->can('view-product-categories')) {
        //     return response()->json([
        //         'error' => 'You don\'t have permission.'
        //     ], 403);
        // }

        $response = $this->service->getAll($request);

        return $this->response($response['data'], $response['count'], true);
    }
}
