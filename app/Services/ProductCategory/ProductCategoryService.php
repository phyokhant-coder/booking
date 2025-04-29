<?php

namespace App\Services\ProductCategory;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\ProductCategoryResource;
use App\Repositories\ProductCategory\ProductCategoryRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryService extends CommonService
{
    protected ProductCategoryRepositoryInterface $productCategoryRepository;

    public function __construct(ProductCategoryRepositoryInterface $productCategoryRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $productCategories = $this->productCategoryRepository->all($params);

        if ($productCategories->isNotEmpty()) {
            $data['data'] = ProductCategoryResource::collection($productCategories)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->productCategoryRepository->totalCount($params);
        }

        return $data;
    }

    public function createProductCategory($request): array
    {
        $input = $this->input($request, $this->productCategoryRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'categories']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->productCategoryRepository->insert($input);

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
    public function getProductCategoryById($id): mixed
    {
        $productCategory = $this->productCategoryRepository->getDataById($id);

        if(!$productCategory) {
            throw new NotFoundResourceException();
        }

        return $productCategory;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateProductCategory($request, $id): array
    {
        $input = $this->input($request, $this->productCategoryRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'categories']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->productCategoryRepository->update($input, $id);

            $productCategory = $this->getProductCategoryById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new ProductCategoryResource($productCategory))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteProductCategory($id): array
    {
        $productCategory = $this->getProductCategoryById($id);

        try {
            DB::beginTransaction();

            $this->productCategoryRepository->destroy($productCategory->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
