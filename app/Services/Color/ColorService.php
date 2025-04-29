<?php

namespace App\Services\Color;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\ColorResource;
use App\Repositories\Color\ColorRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColorService extends CommonService
{
    protected ColorRepositoryInterface $colorRepository;

    public function __construct(ColorRepositoryInterface $colorRepository)
    {
        $this->colorRepository = $colorRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $colors = $this->colorRepository->all($params);

        if ($colors->isNotEmpty()) {
            $data['data'] = ColorResource::collection($colors)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->colorRepository->totalCount($params);
        }

        return $data;
    }

    public function createColor($request): array
    {
        $input = $this->input($request, $this->colorRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->colorRepository->insert($input);

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
    public function getColorById($id): mixed
    {
        $color = $this->colorRepository->getDataById($id);

        if(!$color) {
            throw new NotFoundResourceException();
        }

        return $color;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateColor($request, $id): array
    {
        $input = $this->input($request, $this->colorRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->colorRepository->update($input, $id);

            $color = $this->getColorById($id);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new ColorResource($color))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteColor($id): array
    {
        $color = $this->getColorById($id);

        try {
            DB::beginTransaction();

            $this->colorRepository->destroy($color->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
