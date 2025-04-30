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
use OpenApi\Attributes as OA;

class AdminController extends Controller
{
    protected AdminService $service;
    protected RoleService $roleService;

    public function __construct(AdminService $service, RoleService $roleService)
    {
        $this->service = $service;
        $this->roleService = $roleService;
    }
    
 
    #[OA\Get(
        path: '/api/admin/admins',
        summary: 'List all admins',
        security: [['bearerAuth' => []]],
        tags: ['Admins'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                description: 'Defines how many records should be returned per page (i.e., the number of items displayed in one page of results).',
                required: false,
                in: 'query',
                schema: new OA\Schema(
                    type: 'integer',
                    format: 'integer',
                    example: 20
                )
            ),
            new OA\Parameter(
                name: 'offset',
                description: 'Specifies which page of the results to display. The page number helps navigate through the paginated records.',
                required: false,
                in: 'query',
                schema: new OA\Schema(
                    type: 'integer',
                    format: 'integer',
                    example: 1
                )
            ),
            new OA\Parameter(
                name: 'search',
                description: 'Search value',
                required: false,
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'test'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success'
            )
        ]
    )]

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

    #[OA\Post(
        path: '/api/admin/admins',
        summary: 'Create a new admin',
        security: [['bearerAuth' => []]],
        tags: ['Admins'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'role_id', type: 'integer',  example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Admin created successfully'),
            new OA\Response(response: 422, description: 'Validation error')
        ]
    )]

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

    #[OA\Put(
        path: '/api/admin/admins/{id}',
        summary: 'Update an existing admin',
        security: [['bearerAuth' => []]],
        tags: ['Admins'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'The ID of the admin to update',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'role_id', type: 'integer', example: 1),  // Added role_id property
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Admin updated successfully',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Admin updated successfully',
                        'data' => [
                            'id' => 1,
                            'name' => 'John Doe',
                            'email' => 'john@example.com',
                            'role_id' => 1
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Admin not found',
                content: new OA\JsonContent(
                    example: ['error' => 'Admin not found.']
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'error' => 'Validation failed.',
                        'details' => [
                            'email' => ['The email has already been taken.'],
                            'password' => ['The password must be at least 8 characters.']
                        ]
                    ]
                )
            )
        ]
    )]

    /**
     * Update the requested data to db
     *
     * @param StoreAdminRequest $request
     * @param $id
     * @return JsonResponse
     * @throws NotFoundResourceException
     * @throws Exception
     */

    public function update(StoreAdminRequest $request, $id): JsonResponse
    {
        // Check if the user has permission to update admins
        if (!auth()->user()->can('update-admins')) {
            return response()->json([
                'error' => 'You don\'t have permission.'
            ], 403);
        }
    
        // Validate if the role exists
        $role = $this->roleService->getRoleById($request->get('role_id'));
        if (!$role) {
            return response()->json([
                'error' => 'Role not found.'
            ], 404);
        }
        // Proceed to update the admin
        try {
            $response = $this->service->updateAdmin($request, $id, $role);
            return $this->response($response);
        } catch (NotFoundResourceException $e) {
            // Handle admin not found case
            return response()->json([
                'error' => 'Admin not found.'
            ], 404);
        } catch (Exception $e) {
            // General exception handling
            return response()->json([
                'error' => 'An error occurred during the update.'
            ], 500);
        }
    }
    
    #[OA\Delete(
        path: '/api/admin/admins/{id}',
        summary: 'Delete an admin',
        security: [['bearerAuth' => []]],
        tags: ['Admins'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'The ID of the admin to delete',
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Admin deleted successfully'),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Admin not found'),
        ]
    )]
    
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
