<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\State\StoreStateRequest;
use App\Services\State\StateService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StateController extends Controller
{
    protected StateService $service;

    public function __construct(StateService $service)
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
        if (auth()->guard('admin')->check() && !auth('admin')->user()->can('view-states')) {
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
     * @param StoreStateRequest $request
     * @return JsonResponse
     * @throws NotFoundResourceException
     */
    public function store(StoreStateRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-states')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->createState($request);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }

        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreStateRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreStateRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-states')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->updateState($request, $id);

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
        if (!auth()->user()->can('delete-states')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteState($id);

        return $this->response((array)$response);
    }

    /**
     * Brand Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function stateList(Request $request): JsonResponse
    {
        $response = $this->service->getAll($request);

        return $this->response($response['data'], $response['count'], true);
    }
}
