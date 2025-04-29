<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\PromoCode\StorePromoCodeRequest;
use App\Http\Resources\PromoCodeResource;
use App\Services\PromoCode\PromoCodeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    protected PromoCodeService $service;

    public function __construct(PromoCodeService $service)
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
        if (!auth()->user()->can('view-promo-codes')) {
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
     * @param StorePromoCodeRequest $request
     * @return JsonResponse
     */
    public function store(StorePromoCodeRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-promo-codes')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createPromoCode($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StorePromoCodeRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StorePromoCodeRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-promo-codes')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updatePromoCode($request, $id);

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
        if (!auth()->user()->can('delete-promo-codes')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deletePromoCode($id);

        return $this->response((array)$response);
    }

    /**
     * Check if Promo Code is available or used
     *
     * @param $code
     * @return JsonResponse
     * @throws NotFoundResourceException
     */
    public function checkPromoCode($code): JsonResponse
    {
        $response = $this->service->getPromoCodeByCode($code);

        return response()->json(new PromoCodeResource($response));
    }
}
