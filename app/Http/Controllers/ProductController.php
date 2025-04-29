<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\Product\ProductService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
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
        if (auth()->guard('admin')->check() && !auth()->user()->can('view-products')) {
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
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-products')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createProduct($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreProductRequest $request
     * @param $id
     * @return JsonResponse
     * @throws Exception
     */
    public function update(StoreProductRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-products')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateProduct($request, $id);

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
        if (!auth()->user()->can('delete-products')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteProduct($id);

        return $this->response((array)$response);
    }

    /**
     * @throws NotFoundResourceException
     */
    public function detail($id): JsonResponse
    {
        $product = $this->service->getProductById($id);

        return response()->json(new ProductResource($product));
    }

    /**
     * * @param Request $request
     * @throws NotFoundResourceException
     */
    public function relatedProducts(Request $request): JsonResponse
    {
        $product = $this->service->getProductByCategoryId($request);
        // dd($product);
        return $this->response($product['data'], $product['count'], true);
        
    }
}
