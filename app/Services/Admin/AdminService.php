<?php

namespace App\Services\Admin;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\AdminResource;
use App\Repositories\Admin\AdminRepositoryInterface;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminService extends CommonService
{
    protected AdminRepositoryInterface $adminRepository;
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(AdminRepositoryInterface $adminRepository, RoleRepositoryInterface $roleRepository)
    {
        $this->adminRepository = $adminRepository;
        $this->roleRepository = $roleRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $admins = $this->adminRepository->all($params);

        if ($admins->isNotEmpty()) {
            $data['data'] = AdminResource::collection($admins)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->adminRepository->totalCount($params);
        }

        return $data;
    }

    public function createAdmin($request, $role): array
    {
        $input = $this->input($request, $this->adminRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $admin = $this->adminRepository->insert($input);

            $admin->assignRole($role);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return ['stored' => false, 'message' => $e->getMessage()];
        }

        return ['stored' => true];
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getAdminById($id): mixed
    {
        $admin = $this->adminRepository->getDataById($id);

        if(!$admin) {
            throw new NotFoundResourceException();
        }

        return $admin;
    }

    /**
     * @param $request
     * @param $id
     * @param $role
     * @return array
     * @throws Exception
     */
    public function updateAdmin($request, $id, $role = null): array
    {
        $input = $this->input($request, $this->adminRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->adminRepository->update($input, $id);

            $admin = $this->getAdminById($id);

            if ($role) {
                $admin->roles()->detach();
                $admin->assignRole($role);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new AdminResource($admin))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteAdmin($id): array
    {
        $admin = $this->getAdminById($id);

        try {
            DB::beginTransaction();

            $this->adminRepository->destroy($admin->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
