<?php

namespace App\Services\Cart;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\CartResource;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Support\Facades\DB;

class CartService extends CommonService
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));
        $params['with'] = ['product.productCategory', 'product.productBrand', 'user', 'productVariant', 'productVariantDetail'];
        if (auth()->guard('api')->check()) {
            $params['user_id'] = auth()->guard('api')->id();
        }

        $carts = $this->cartRepository->all($params);

        if ($carts->isNotEmpty()) {
            $data['data'] = CartResource::collection($carts)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->cartRepository->totalCount($params);
        }

        return $data;
    }

    public function createCart($request): array
    {
        $input = $this->input($request, $this->cartRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);
            if (auth()->guard('api')->check()) {
                $input['user_id'] = auth()->guard('api')->id();
            }

            $exists = $this->cartRepository->connection()->query()
                ->where('product_id', $input['product_id'])
                ->where('product_variant_id', $input['product_variant_id'])
                ->where('product_variant_detail_id', $input['product_variant_detail_id'])
                ->where('user_id', $input['user_id'])
                ->exists();

            if ($exists) {
                return ['stored' => false, 'message' => 'Item already exists in cart.'];
            }

            $this->cartRepository->insert($input);

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
    public function getCartById($id): mixed
    {
        $cart = $this->cartRepository->getDataById($id);

        if(!$cart) {
            throw new NotFoundResourceException();
        }

        return $cart;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateCart($request, $id): array
    {
        $input = $this->input($request, $this->cartRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->cartRepository->update($input, $id);

            $cart = $this->getCartById($id);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new CartResource($cart))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteCart($id): array
    {
        $cart = $this->getCartById($id);

        try {
            DB::beginTransaction();

            $this->cartRepository->destroy($cart->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
