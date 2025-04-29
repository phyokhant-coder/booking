<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\Country\StoreCountryRequest;
use App\Services\Country\CountryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected CountryService $service;

    public function __construct(CountryService $service)
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
        if (auth()->guard('admin')->check() && !auth('admin')->user()->can('view-countries')) {
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
     * @param StoreCountryRequest $request
     * @return JsonResponse
     * @throws NotFoundResourceException
     */
    public function store(StoreCountryRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-countries')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createCountry($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreCountryRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreCountryRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-countries')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateCountry($request, $id);

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
        if (!auth()->user()->can('delete-countries')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteCountry($id);

        return $this->response((array)$response);
    }

    /**
     * Brand Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function countryList(Request $request): JsonResponse
    {
        $response = $this->service->getAll($request);

        return $this->response($response['data'], $response['count'], true);
    }
}
