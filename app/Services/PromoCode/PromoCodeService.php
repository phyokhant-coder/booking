<?php

namespace App\Services\PromoCode;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\PromoCodeResource;
use App\Repositories\PromoCode\PromoCodeRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromoCodeService extends CommonService
{
    protected PromoCodeRepositoryInterface $promoCodeRepository;

    public function __construct(PromoCodeRepositoryInterface $promoCodeRepository)
    {
        $this->promoCodeRepository = $promoCodeRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $promoCodes = $this->promoCodeRepository->all($params);

        if ($promoCodes->isNotEmpty()) {
            $data['data'] = PromoCodeResource::collection($promoCodes)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->promoCodeRepository->totalCount($params);
        }

        return $data;
    }

    /**
     * generate promo code
     *
     * @param int $length
     * @return string
     */
    public function generateUniquePromoCode(int $length = 6): string
    {
        do {
            $promoCode = Str::upper(Str::random($length));
        } while ($this->promoCodeRepository->getDataByCode($promoCode));

        return $promoCode;
    }

    public function createPromoCode($request): array
    {
        $input = $this->input($request, $this->promoCodeRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $input['code'] = $this->generateUniquePromoCode();

            $this->promoCodeRepository->insert($input);

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
    public function getPromoCodeById($id): mixed
    {
        $promoCode = $this->promoCodeRepository->getDataById($id);

        if(!$promoCode) {
            throw new NotFoundResourceException();
        }

        return $promoCode;
    }

    /**
     * @param $code
     * @return mixed
     * @throws NotFoundResourceException
     */
    public function getPromoCodeByCode($code): mixed
    {
        $promoCode = $this->promoCodeRepository->getDataByCode($code);

        if(!$promoCode) {
            throw new NotFoundResourceException();
        }

        return $promoCode;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updatePromoCode($request, $id): array
    {
        $input = $this->input($request, $this->promoCodeRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->promoCodeRepository->update($input, $id);

            $promoCode = $this->getPromoCodeById($id);


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new PromoCodeResource($promoCode))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deletePromoCode($id): array
    {
        $promoCode = $this->getPromoCodeById($id);

        try {
            DB::beginTransaction();

            $this->promoCodeRepository->destroy($promoCode->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
