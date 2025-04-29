<?php

namespace App\Services\Order;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Enums\OrderStatus;
use App\Enums\PromoCodeStatus;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Mail\OrderConfirmationEmail;
use App\Models\OrderPromoCode;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\BillingAddress\BillingAddressRepositoryInterface;
use App\Repositories\OrderLine\OrderLineRepositoryInterface;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductVariantDetail\ProductVariantDetailRepositoryInterface;
use App\Repositories\PromoCode\PromoCodeRepositoryInterface;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderService extends CommonService
{
    protected OrderRepositoryInterface $orderRepository;
    protected BillingAddressRepositoryInterface $billingAddressRepository;
    protected OrderLineRepositoryInterface $orderLineRepository;
    protected ProductRepositoryInterface $productRepository;
    protected ProductVariantDetailRepositoryInterface $productVariantDetailRepository;
    protected PaymentRepositoryInterface $paymentRepository;
    protected PromoCodeRepositoryInterface $promoCodeRepository;
    protected CartRepositoryInterface $cartRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        BillingAddressRepositoryInterface $billingAddressRepository,
        OrderLineRepositoryInterface $orderLineRepository,
        ProductRepositoryInterface $productRepository,
        PaymentRepositoryInterface $paymentRepository,
        ProductVariantDetailRepositoryInterface $productVariantDetailRepository,
        PromoCodeRepositoryInterface $promoCodeRepository,
        CartRepositoryInterface $cartRepository,
    )
    {
        $this->orderRepository = $orderRepository;
        $this->orderLineRepository = $orderLineRepository;
        $this->billingAddressRepository = $billingAddressRepository;
        $this->productRepository = $productRepository;
        $this->paymentRepository = $paymentRepository;
        $this->productVariantDetailRepository = $productVariantDetailRepository;
        $this->promoCodeRepository = $promoCodeRepository;
        $this->cartRepository = $cartRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));
        if (auth()->guard('api')->check()) {
            $params['user_id'] = auth()->guard('api')->id();
        }
        $params['with'] = [
            'user',
            'billingAddress',
            'billingAddress.user',
            'billingAddress.country',
            'billingAddress.state',
            'orderLines.product.productCategory',
            'orderLines.product.productBrand',
            'orderLines.productVariant',
            'orderLines.productVariantDetail'
        ];

        $orders = $this->orderRepository->all($params);

        if ($orders->isNotEmpty()) {
            $data['data'] = OrderResource::collection($orders)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->orderRepository->totalCount($params);
        }

        return $data;
    }

    public function createOrder($request): array
    {
        $input = $this->input($request, $this->orderRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            if (auth()->guard('api')->check()) {
                $prepareInput['user_id'] = auth()->guard('api')->id();
            }
            $prepareInput['order_no'] = $this->generateOrderNumber();
            $prepareInput['order_date'] = now();
            $prepareInput['status'] = "PENDING";

            $input = array_merge($input, $prepareInput);

            $order = $this->orderRepository->insert($input);

            if ($request->promo_code_id) {
                OrderPromoCode::create([
                    'order_id' => $order->id,
                    'promo_code_id' => $request->promo_code_id
                ]);

                $this->promoCodeRepository->update(['status' => PromoCodeStatus::USED->value], $request->promo_code_id);
            }

            if (auth()->guard('api')->check()) {
                foreach ($request->products as $product) {
                    $orderLineReq['order_line_no'] = 'ORDL-' . strtoupper(uniqid());
                    $orderLineReq['order_id'] = $order->id;
                    $orderLineReq['product_id'] = $product['id'];
                    $orderLineReq['product_variant_id'] = $product['product_variant_id'];
                    $orderLineReq['product_variant_detail_id'] = $product['product_variant_detail_id'];
                    $orderLineReq['quantity'] = $product['quantity'];
                    $orderLineReq['price'] = $product['price'];
                    $orderLineReq['total_price'] = $product['total_price'];

                    $this->orderLineRepository->insert($orderLineReq);

                    if ($product['product_variant_detail_id']) {
                        $productDetail = $this->productVariantDetailRepository->getDataById($product['product_variant_detail_id']);
                        $qty = $productDetail->quantity - $product['quantity'];
                        $productDetail->update(['quantity' => $qty]);
                    } else {
                        $existingProduct = $this->productRepository->getDataById($product['product_id']);
                        $qty = $existingProduct->quantity - $product['quantity'];
                        $existingProduct->update(['quantity' => $qty]);
                    }
                }
            }

            if ($request->payment_method_id && $request->hasFile('screenshot_image_url')) {
                $request->merge(['directory' => 'payments']);
                $prePaymentReq = $this->prepareInput($request);
                // dd($prePaymentReq);
                $paymentReq = [
                    'order_id' => $order->id,
                    'payment_method_id' => $request['payment_method_id'],
                    'screenshot_image_url' => $prePaymentReq['screenshot_image_url'],
                ];
                $this->paymentRepository->insert($paymentReq);
            }

            if (!empty($prepareInput['user_id'])) {
                try {
                    DB::table('carts')->where('user_id', $prepareInput['user_id'])->delete();
                } catch (\Exception $e) {
                    \Log::error("Cart deletion failed for user ID: " . $prepareInput['user_id'], ['error' => $e->getMessage()]);
                }
            }            

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return ['stored' => false, 'message' => $e->getMessage()];
        }

        return ['stored' => true];
    }

    /**
     * @param $id
     * @param array $relations
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getOrderById($id, array $relations = []): mixed
    {
        $order = $this->orderRepository->getDataById($id, $relations);

        if(!$order) {
            throw new NotFoundResourceException();
        }

        return $order;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateOrder($request, $id): array
    {
        $input = $this->input($request, $this->orderRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->orderRepository->update($input, $id);

            $order = $this->getOrderById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new OrderResource($order))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteOrder($id): array
    {
        $order = $this->getOrderById($id);

        try {
            DB::beginTransaction();

            $this->orderRepository->destroy($order->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateOrderChangeStatus($request, $id): array
    {
        try {
            DB::beginTransaction();

            // Extract only the status field from the request
            $status = $request->input('status');

            if (!$status) {
                throw new Exception('Status is required');
            }

            // Update only the status field in the order
            $response = $this->orderRepository->update(['status' => $status], $id);

            $order = $this->getOrderById($id, ['user', 'billingAddress']);

            // send confirmation email
            if ($response && $status === OrderStatus::CONFIRMED->value) {
                $orderLines = $this->orderLineRepository->all(['order_id' => $id]);
                foreach($orderLines as $orderLine)
                {
                    $variantDetail = $this->productVariantDetailRepository->getDataById($orderLine->product_variant_detail_id);
                    if ($variantDetail->quantity < $orderLine->quantity) {
                        throw new \Exception("Insufficient stock for product variant ID: " . $orderLine->product_variant_detail_id);
                    }
                    $updateQuantity = $variantDetail->quantity - $orderLine->quantity;
                    $this->productVariantDetailRepository->update(['quantity' => $updateQuantity], $orderLine->product_variant_detail_id);
                };
                $email = $order->user?->email ?? $order->billingAddress?->email;
                Mail::to($email)->send(new OrderConfirmationEmail($order));
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }

        return (new OrderResource($order))->toArray($request);
    }

    /**
     * @param $request
     * @return array
     * @throws Exception
     */
    public function storeGuestUserOrder($request): array
    {
        $input = $this->input($request, $this->orderRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);
            $billingAddressInput['first_name'] = $request->first_name;
            $billingAddressInput['last_name'] = $request->last_name;
            $billingAddressInput['country_id'] = $request->country_id;
            $billingAddressInput['email'] = $request->email;
            $billingAddressInput['phone_number'] = $request->phone_number;
            $billingAddressInput['state_id'] = $request->state_id;
            $billingAddressInput['full_address'] = $request->full_address;
            $billingAddress = $this->billingAddressRepository->insert($billingAddressInput);

            $prepareInput['billing_address_id'] = $billingAddress->id;
            $prepareInput['order_no'] = $this->generateOrderNumber(); // Assuming a method to generate order numbers
            $prepareInput['order_date'] = now(); // Using the current date and time
            $prepareInput['status'] = "PENDING";

            $input = array_merge($input, $prepareInput);

            $order = $this->orderRepository->insert($input);

            if ($request->promo_code_id) {
                OrderPromoCode::create([
                    'order_id' => $order->id,
                    'promo_code_id' => $request->promo_code_id
                ]);

                $this->promoCodeRepository->update(['status' => PromoCodeStatus::USED->value], $request->promo_code_id);
            }

            if ($request->payment_method_id && $request->hasFile('screenshot_image_url')) {
                $request->merge(['directory' => 'payments']);
                $prePaymentReq = $this->prepareInput($request);
                $paymentReq = [
                    'order_id' => $order->id,
                    'payment_method_id' => $request['payment_method_id'],
                    'screenshot_image_url' => $prePaymentReq['screenshot_image_url'],
                ];
                $this->paymentRepository->insert($paymentReq);
            }

            foreach ($request->products as $product) {
                $orderLineReq['order_line_no'] = 'ORDL-' . strtoupper(uniqid());
                $orderLineReq['order_id'] = $order->id;
                $orderLineReq['product_id'] = $product['id'];
                $orderLineReq['product_variant_id'] = $product['product_variant_id'];
                $orderLineReq['product_variant_detail_id'] = $product['product_variant_detail_id'];
                $orderLineReq['quantity'] = $product['quantity'];
                $orderLineReq['price'] = $product['price'];
                $orderLineReq['total_price'] = $product['total_price'];

                $this->orderLineRepository->insert($orderLineReq);

                if ($product['product_variant_detail_id']) {
                    $productDetail = $this->productVariantDetailRepository->getDataById($product['product_variant_detail_id']);
                    $qty = $productDetail->quantity - $product['quantity'];
                    $productDetail->update(['quantity' => $qty]);
                } else {
                    $existingProduct = $this->productRepository->getDataById($product['product_id']);
                    $qty = $existingProduct->quantity - $product['quantity'];
                    $existingProduct->update(['quantity' => $qty]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new OrderResource($order))->toArray($request);
    }

     /**
     * @return array
     * @throws Exception
     */
    public function pendingOrderCount(): array
    {
        try {
            DB::beginTransaction();


            // Update only the status field in the order
            $response = $this->orderRepository->all(['status' => OrderStatus::PENDING->value]);
            $count = $response->count();

            DB::commit();
            return ['count' => $count];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

     /**
     * @return array
     * @throws Exception
     */
    public function confirmOrderCount(): array
    {
        try {
            DB::beginTransaction();


            // Update only the status field in the order
            $response = $this->orderRepository->all(['status' => OrderStatus::CONFIRMED->value]);
            $count = $response->count();

            DB::commit();
            return ['count' => $count];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

     /**
     * @return array
     * @throws Exception
     */
    public function cancelOrderCount(): array
    {
        try {
            DB::beginTransaction();


            // Update only the status field in the order
            $response = $this->orderRepository->all(['status' => OrderStatus::CANCEL->value]);
            $count = $response->count();

            DB::commit();
            return ['count' => $count];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function shippedOrderCount(): array
    {
        try {
            DB::beginTransaction();


            // Update only the status field in the order
            $response = $this->orderRepository->all(['status' => OrderStatus::SHIPPED->value]);
            $count = $response->count();

            DB::commit();
            return ['count' => $count];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function deliveredOrderCount(): array
    {
        try {
            DB::beginTransaction();


            // Update only the status field in the order
            $response = $this->orderRepository->all(['status' => OrderStatus::DELIVERED->value]);
            $count = $response->count();

            DB::commit();
            return ['count' => $count];
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function monthlyBestSellingProductList($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $params['with'] = ['productImages', 'productCategory', 'productBrand', 'productVariants.productVariantDetails'];
        // $params['last-month'] = $lastMonth;

        $monthlyBestSellingProductList = DB::table('order_lines')
            ->select(
            'products.id as product_id',
            'products.name as product_name',
            DB::raw('SUM(order_lines.quantity) as total_quantity'),
            DB::raw('SUM(order_lines.total_price) as total_price')
        )
        ->join('orders', 'order_lines.order_id', '=', 'orders.id')
        ->join('products', 'order_lines.product_id', '=', 'products.id')
        ->whereRaw('DATE_FORMAT(orders.order_date, "%Y-%m") = ?', [$lastMonth])
        ->groupBy('products.id')
        ->orderBy('total_price', 'desc')
        ->pluck('product_id')
        ->toArray();
        $params["product_ids"]= $monthlyBestSellingProductList;
        $params = array_merge($params, $this->getFilterParams($request));
        // dd($monthlyBestSellingProductList);
        $products = $this->productRepository->all($params);

        if ($products->isNotEmpty()) {
            $data['data'] = ProductResource::collection($products)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->productRepository->totalCount($params);
        }

        return $data;
    }

    /**
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getOrderDetailByUserId($request): mixed
    {
        $data = ['data' => [], 'count' => 0];
        $params = $request->all();
        $params = array_merge($params, $this->getFilterParams($request));
        if (auth()->guard('api')->check()) {
            $params['user_id'] = auth()->guard('api')->id();
        }
        $params['with'] = [
            'user',
            'billingAddress',
            'billingAddress.user',
            'billingAddress.country',
            'billingAddress.state',
            'orderLines.product.productCategory',
            'orderLines.product.productBrand',
            'orderLines.productVariant',
            'orderLines.productVariantDetail'
        ];

        $orders = $this->orderRepository->all($params);

        if ($orders->isNotEmpty()) {
            $data['data'] = OrderResource::collection($orders)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->orderRepository->totalCount($params);
        }

        return $data;
    }
}
