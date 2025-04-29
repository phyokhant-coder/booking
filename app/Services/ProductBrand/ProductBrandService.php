<?php

namespace App\Services\ProductBrand;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\ProductBrandResource;
use App\Repositories\ProductBrand\ProductBrandRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBrandService extends CommonService
{
    protected ProductBrandRepositoryInterface $productBrandRepository;

    public function __construct(ProductBrandRepositoryInterface $productBrandRepository)
    {
        $this->productBrandRepository = $productBrandRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $productBrands = $this->productBrandRepository->all($params);

        if ($productBrands->isNotEmpty()) {
            $data['data'] = ProductBrandResource::collection($productBrands)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->productBrandRepository->totalCount($params);
        }

        return $data;
    }

    public function createProductBrand($request): array
    {
        $input = $this->input($request, $this->productBrandRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'brands']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->productBrandRepository->insert($input);

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
    public function getProductBrandById($id): mixed
    {
        $productBrand = $this->productBrandRepository->getDataById($id);

        if(!$productBrand) {
            throw new NotFoundResourceException();
        }

        return $productBrand;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateProductBrand($request, $id): array
    {
        $input = $this->input($request, $this->productBrandRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'brands']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->productBrandRepository->update($input, $id);

            $productBrand = $this->getProductBrandById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new ProductBrandResource($productBrand))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteProductBrand($id): array
    {
        $productBrand = $this->getProductBrandById($id);

        try {
            DB::beginTransaction();

            $this->productBrandRepository->destroy($productBrand->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
