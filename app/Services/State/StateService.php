<?php

namespace App\Services\State;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\StateResource;
use App\Repositories\State\StateRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StateService extends CommonService
{
    protected StateRepositoryInterface $stateRepository;

    public function __construct(StateRepositoryInterface $stateRepository)
    {
        $this->stateRepository = $stateRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));
        $params['with'] = [
            'country'
        ];

        $states = $this->stateRepository->all($params);

        if ($states->isNotEmpty()) {
            $data['data'] = StateResource::collection($states)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->stateRepository->totalCount($params);
        }

        return $data;
    }

    public function createState($request): array
    {
        $input = $this->input($request, $this->stateRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->stateRepository->insert($input);

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
    public function getStateById($id): mixed
    {
        $state = $this->stateRepository->getDataById($id);

        if(!$state) {
            throw new NotFoundResourceException();
        }

        return $state;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateState($request, $id): array
    {
        $input = $this->input($request, $this->stateRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->stateRepository->update($input, $id);

            $state = $this->getStateById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new StateResource($state))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteState($id): array
    {
        $state = $this->getStateById($id);

        try {
            DB::beginTransaction();

            $this->stateRepository->destroy($state->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
