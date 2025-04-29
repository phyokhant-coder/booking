<?php

namespace App\Services\User;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\UserResource;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserService extends CommonService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $users = $this->userRepository->all($params);

        if ($users->isNotEmpty()) {
            $data['data'] = UserResource::collection($users)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->userRepository->totalCount($params);
        }

        return $data;
    }

    public function createUser($request): array
    {
        $input = $this->input($request, $this->userRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $user = $this->userRepository->insert($input);

            DB::commit();
        } catch (Exception $e) {
            dd($e);
            DB::rollBack();
            return ['stored' => false, 'message' => $e->getMessage()];
        }

        return ['stored' => true, 'user' => $user];
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getUserById($id): mixed
    {
        $user = $this->userRepository->getDataById($id);

        if(!$user) {
            throw new NotFoundResourceException();
        }

        return $user;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateUser($request, $id): array
    {
        $input = $this->input($request, $this->userRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->userRepository->update($input, $id);

            $user = $this->getUserById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new UserResource($user))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteUser($id): array
    {
        $user = $this->getUserById($id);

        try {
            DB::beginTransaction();

            $this->userRepository->destroy($user->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
