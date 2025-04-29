<?php

namespace App\Api\Foundation\Routing;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

/**
 * Class Formatter
 * @author Myo Thant Kyaw <myothantkyaw.dev@gmail.com>
 * @package Rest API Response Formatter
 */
class Formatter
{
    public static $instance;

    protected $count;

    protected $total;

    protected $message;

    protected $offset = 0;

    protected $limit = 10;

    protected $success = 1;

    protected $status = 200;

    protected $method = 'get';

    protected $totalCount = 0;

    protected $filterCount = 0;

    protected $tokenType = 'Bearer';

    public static function factory(): Formatter
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();

            $method = self::$instance->getMethod();

            if ($method === 'post') {
                self::$instance->status = 201;
            }
        }

        return self::$instance;
    }

    protected function getMethod(): string
    {
        $method = Request::method();

        $this->method = $method;

        return strtolower($method);
    }

    public function make(array $data = [], int $totalCount = 0, bool $wantInfo = false): array
    {
        $this->count = count($data);

        $this->totalCount = $totalCount ?: $this->count;

        $format = $this->defaultFormat($wantInfo);
        empty($this->message) ?
            $format["data"] = $data :
            $format['message'] = $this->message;

        return $format;
    }

    public function defaultFormat($wantInfo = false): array
    {
        return [
            'success' => $this->success,
            'metadata' => $this->getMeta($wantInfo)
        ];
    }

    public function makeTokenResponse($token, $expTime, $tokenType = null, array $data = []): array
    {
        $this->status = 200;

        $response = $this->defaultFormat();

        $response['token'] = [
            'type' => $tokenType ?? $this->tokenType,
            'access_token' => $token,
            'expired_at' => $expTime,
        ];

        empty($data) ?: $response["data"] = $data;

        return $response;
    }

    public function error($message): array
    {
        $this->success = 0;
        $this->status = 400;
        $this->message = $message;

        return $this->defaultFormat();
    }

    /**
     * @param $exception
     * @param $code
     * @param null $customCode
     * @return JsonResponse
     */
    public function throwException($exception, $code, $customCode = null): JsonResponse
    {
        $code = in_array($code, $this->statusCodes()) ? $code : 500;

        $this->success = 0;

        $this->status = $customCode ?? $code;

        $response = $this->defaultFormat();

        $error = json_decode($exception);

        if (is_string($exception) &&
            json_last_error() == JSON_ERROR_NONE) {

            $response['errors'] = $error;
        } else {
            $response['message'] = $exception;
        }

        return response()->json($response, $code);
    }

    public function setStatus(int $status): Formatter
    {
        $this->status = $status;

        return $this;
    }

    protected function statusCodes(): array
    {
        return [
            400, 403, 401, 404, 405, 500,
        ];
    }

    public function setTotalCount(int $count): Formatter
    {
        $this->totalCount = $count;

        return $this;
    }

    public function getMeta($wantInfo): array
    {
        $method = $this->getMethod();
        $metadata['status'] = $this->status;
        $metadata['method'] = strtoupper($method);

        if ($method !== 'post' && $wantInfo) {
            $metadata['info'] = [
                'count' => (int)$this->count,
                'total' => (int)$this->totalCount,
                'limit' => (int)request('limit') ?: $this->limit,
                'offset' => (int)request('offset') ?: $this->offset,
            ];
        }

        return $metadata;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    protected function __clone()
    {
    }

    protected function __construct()
    {
    }
}
