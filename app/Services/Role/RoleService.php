<?php

namespace App\Services\Role;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\CommonService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Repositories\Admin\AdminRepositoryInterface;
use App\Api\Foundation\Exceptions\NotFoundResourceException;

class RoleService extends CommonService
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getRoles($request)
    {
        $params = $this->params($request);

        $params = array_merge($params, [
            'guard_name' => $this->currentGuard()
        ]);

        return $this->roleRepository->all($params);
    }

    /**
     * @param $id
     * @return Builder|Model|object
     * @throws NotFoundResourceException
     */
    public function getRoleById($id)
    {
        $role = $this->roleRepository->getDataById($id);

        if (!$role) {
            throw new NotFoundResourceException();
        }

        return $role;
    }
}
