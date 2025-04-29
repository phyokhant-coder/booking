<?php

namespace App\Services\Country;

use App\Api\Foundation\Exceptions\NotFoundResourceException;
use App\Http\Resources\CountryResource;
use App\Repositories\Country\CountryRepositoryInterface;
use App\Services\CommonService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryService extends CommonService
{
    protected CountryRepositoryInterface $countryRepository;

    public function __construct(CountryRepositoryInterface $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function getAll($request)
    {
        $data = ['data' => [], 'count' => 0];
        $params = $this->params($request);
        $params = array_merge($params, $this->getFilterParams($request));

        $countries = $this->countryRepository->all($params);

        if ($countries->isNotEmpty()) {
            $data['data'] = CountryResource::collection($countries)->toArray($request);

            unset($params['limit'], $params['offset']);

            $data['count'] = $this->countryRepository->totalCount($params);
        }

        return $data;
    }

    public function createCountry($request): array
    {
        $input = $this->input($request, $this->countryRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->countryRepository->insert($input);

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
    public function getCountryById($id): mixed
    {
        $country = $this->countryRepository->getDataById($id);

        if(!$country) {
            throw new NotFoundResourceException();
        }

        return $country;
    }

    /**
     * @param $request
     * @param $id
     * @return array
     * @throws Exception
     */
    public function updateCountry($request, $id): array
    {
        $input = $this->input($request, $this->countryRepository->connection()->getFillable());

        try {
            DB::beginTransaction();

            $prepareInput = $this->prepareInput($request);

            $input = array_merge($input, $prepareInput);

            $this->countryRepository->update($input, $id);

            $country = $this->getCountryById($id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }

        return (new CountryResource($country))->toArray($request);
    }

    /**
     * @param $id
     * @return true[]
     * @throws NotFoundResourceException
     */
    public function deleteCountry($id): array
    {
        $country = $this->getCountryById($id);

        try {
            DB::beginTransaction();

            $this->countryRepository->destroy($country->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return ['deleted' => false, 'message' => $e->getMessage()];
        }

        return ['deleted' => true];
    }
}
