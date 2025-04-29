<?php

namespace App\Services\Product;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\ProductResource;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductImage\ProductImageRepositoryInterface;
use App\Repositories\ProductVariant\ProductVariantRepositoryInterface;
use App\Repositories\ProductVariantDetail\ProductVariantDetailRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService extends CommonService
{
    protected ProductRepositoryInterface $productRepository;
    protected ProductImageRepositoryInterface $productImageRepository;
    protected ProductVariantRepositoryInterface $productVariantRepository;
    protected ProductVariantDetailRepositoryInterface $productVariantDetailRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductImageRepositoryInterface $productImageRepository,
        ProductVariantRepositoryInterface $productVariantRepository,
        ProductVariantDetailRepositoryInterface $productVariantDetailRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->productImageRepository = $productImageRepository;
        $this->productVariantRepository = $productVariantRepository;
        $this->productVariantDetailRepository = $productVariantDetailRepository;
    }

    public function getAll($request): array
    {
        $data = ['data' => [], 'count' => 0];
        $params = $request->all();
        $params['with'] = ['productImages', 'productCategory', 'productBrand', 'productVariants.productVariantDetails'];
        $params = array_merge($params, $this->getFilterParams($request));
        $products = $this->productRepository->all($params);

        if ($products->isNotEmpty()) {
            $data['data'] = ProductResource::collection($products)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->productRepository->totalCount($params);
        }

        return $data;
    }

    public function createProduct($request): array
    {
        $input = $this->input($request, $this->productRepository->connection()->getFillable());
        $input['product_code'] = $this->generateProductCode();

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'products']);
            $prepareInput = $this->prepareInput($request);
            $input = array_merge($input, $prepareInput);

            $product = $this->productRepository->insert($input);

            $this->storeProductImages($request, $product);

            $this->storeProductVariants($request, $product);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return ['stored' => false, 'message' => $e->getMessage()];
        }

        return ['stored' => true];
    }

    public function generateProductCode($length = 8): string
    {
        return strtoupper(Str::random($length));
    }

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getProductById($id): mixed
    {
        $product = $this->productRepository->getDataById($id, ['productCategory', 'productBrand', 'productImages', 'productVariants.productVariantDetails']);

        if(!$product) {
            throw new NotFoundResourceException();
        }

        return $product;
    }

    /**
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getProductByCategoryId($request): mixed
    {
        $data = ['data' => [], 'count' => 0];
        $params = $request->all();
        $params['with'] = ['productImages', 'productCategory', 'productBrand', 'productVariants.productVariantDetails'];
        $params = array_merge($params, $this->getFilterParams($request));
        $products = $this->productRepository->all($params);
        
        if ($products->isNotEmpty()) {
            $data['data'] = ProductResource::collection($products)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->productRepository->totalCount($params);
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    public function storeProductImages($request, $product): void
    {
        if ($request->hasFile('product_images')) {
            foreach ($request->file('product_images') as $image) {
                $path = $image->store("products", 'public');

                if (!$path) {
                    throw new Exception("Failed to store product image.");
                }

                $productImageInput = [
                    'product_id' => $product->id,
                    'image_url' => str_replace("products/", '', $path),
                ];
                $this->productImageRepository->insert($productImageInput);
            }
        }
    }

    public function storeProductVariants($request, $product): void
    {
        if ($request->product_variants) {
            foreach ($request->product_variants as $variant) {
                if (isset($variant['image_url']) && $variant['image_url'] instanceof UploadedFile) {
                    $path = $variant['image_url']->store("products", 'public');
                    $variantImageUrl = str_replace("products/", '', $path);
                } else {
                    $variantImageUrl = null;
                }
                $productVariantInput = [
                    'product_id' => $product->id,
                    'color' => $variant['color'],
                    'image_url' => $variantImageUrl,
                ];


                $productVariant = $this->productVariantRepository->insert($productVariantInput);

                if ($variant['product_variant_details']) {
                    foreach ($variant['product_variant_details'] as $variantDetail) {
                        $productVariantDetailInput = [
                            'product_variant_id' => $productVariant->id,
                            'size' => $variantDetail['size'],
                            'quantity' => $variantDetail['quantity'],
                            'price' => $variantDetail['price'],
                        ];

                        $this->productVariantDetailRepository->insert($productVariantDetailInput);
                    }
                }
            }
        }
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateProduct($request, $id): array
    {
        $input = $this->input($request, $this->productRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $request->merge(['directory' => 'products']);

            $prepareInput = $this->prepareInput($request);
            $input = array_merge($input, $prepareInput);
            $this->productRepository->update($input, $id);

            $product = $this->productRepository->getDataById($id, ['productCategory', 'productBrand', 'productImages']);

            if ($product->productImages && $request->hasFile('product_images')) {
                foreach ($product->productImages as $existingImage) {
                    $existingImagePath = public_path('storage/products/' . $existingImage->image_url);
                    if (file_exists($existingImagePath)) {
                        unlink($existingImagePath);
                    }
                    $existingImage->delete();
                }
            }

            $this->storeProductImages($request, $product);

            if ($product->productVariants && $request->product_variants) {
                foreach ($product->productVariants as $existingVariant) {
                    $existingVariant->forceDelete();
                }
            }

            $this->storeProductVariants($request, $product);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new ProductResource($product))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteProduct($id): array
    {
        $product = $this->getProductById($id);

        try {
            DB::beginTransaction();

            $this->productRepository->destroy($product->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
