<?php

namespace App\Services\Size;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\SizeResource;
use App\Repositories\Size\SizeRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SizeService extends CommonService
{
    protected SizeRepositoryInterface $sizeRepository;

    public function __construct(SizeRepositoryInterface $sizeRepository)
    {
        $this->sizeRepository = $sizeRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $sizes = $this->sizeRepository->all($params);

        if ($sizes->isNotEmpty()) {
            $data['data'] = SizeResource::collection($sizes)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->sizeRepository->totalCount($params);
        }

        return $data;
    }

    public function createSize($request): array
    {
        $input = $this->input($request, $this->sizeRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->sizeRepository->insert($input);

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
    public function getSizeById($id): mixed
    {
        $size = $this->sizeRepository->getDataById($id);

        if(!$size) {
            throw new NotFoundResourceException();
        }

        return $size;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateSize($request, $id): array
    {
        $input = $this->input($request, $this->sizeRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->sizeRepository->update($input, $id);

            $size = $this->getSizeById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new SizeResource($size))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteSize($id): array
    {
        $size = $this->getSizeById($id);

        try {
            DB::beginTransaction();

            $this->sizeRepository->destroy($size->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
