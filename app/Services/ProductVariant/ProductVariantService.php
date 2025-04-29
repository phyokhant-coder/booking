<?php

namespace App\Services\ProductVariant;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\ProductVariantResource;
use App\Repositories\ProductVariant\ProductVariantRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantService extends CommonService
{
    protected ProductVariantRepositoryInterface $productVariantRepository;

    public function __construct(ProductVariantRepositoryInterface $productVariantRepository)
    {
        $this->productVariantRepository = $productVariantRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $productVariants = $this->productVariantRepository->all($params);

        if ($productVariants->isNotEmpty()) {
            $data['data'] = ProductVariantResource::collection($productVariants)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->productVariantRepository->totalCount($params);
        }

        return $data;
    }

    public function createProductVariant($request): array
    {
        $input = $this->input($request, $this->productVariantRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'variants']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->productVariantRepository->insert($input);

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
    public function getProductVariantById($id): mixed
    {
        $productVariant = $this->productVariantRepository->getDataById($id);

        if(!$productVariant) {
            throw new NotFoundResourceException();
        }

        return $productVariant;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateProductVariant($request, $id): array
    {
        $input = $this->input($request, $this->productVariantRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'variants']);

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->productVariantRepository->update($input, $id);

            $productVariant = $this->getProductVariantById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new ProductVariantResource($productVariant))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteProductVariant($id): array
    {
        $productVariant = $this->getProductVariantById($id);

        try {
            DB::beginTransaction();

            $this->productVariantRepository->destroy($productVariant->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
