<?php

namespace App\Http\Controllers;
use OpenApi\Attributes as OA;

#[OA\Info(version: "1.0.0", description: "Shopping Api", title: "Shopping Documentation")]
#[OA\Server(url: 'http://localhost:8001', description: "local server")]
#[OA\Server(url: 'http://staging.example.com', description: "staging server")]
#[OA\Server(url: 'http://example.com', description: "production server")]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: "http", name: "Authorization", in: "header", scheme: "bearer")]

class Controller extends \App\Api\Foundation\Routing\Controller
{
}