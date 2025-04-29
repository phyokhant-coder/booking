<?php

namespace App\Http\Controllers;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Api\Foundation\Routing\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Services\Admin\AdminService;
use App\Services\Role\RoleService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    protected AdminService $service;
    protected RoleService $roleService;

    public function __construct(AdminService $service, RoleService $roleService)
    {
        $this->service = $service;
        $this->roleService = $roleService;
    }

    /**
     * Listing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (!auth()->user()->can('view-admins')) {
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
     * @param StoreAdminRequest $request
     * @return JsonResponse
     * @throws NotFoundResourceException
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        if (!auth()->user()->can('create-admins')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $role = $this->roleService->getRoleById($request->get('role_id'));

        $response = $this->service->createAdmin($request, $role);

        if (!$response['stored']) {
            return $this->response($response, 0, false, 500);
        }
        return $this->response($response);
    }

    /**
     * update the requested data to db
     *
     * @param StoreAdminRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */
    public function update(StoreAdminRequest $request, $id): JsonResponse
    {
        if (!auth()->user()->can('update-admins')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $role = $this->roleService->getRoleById($request->get('role_id'));

        $response = $this->service->updateAdmin($request, $id, $role);

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
        if (!auth()->user()->can('delete-admins')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }

        $response = $this->service->deleteAdmin($id);

        return $this->response((array)$response);
    }
}
