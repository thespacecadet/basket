<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="Shopping Basket API", version="1.0")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
