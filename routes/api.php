<?php

use App\Http\Controllers\BasketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::group(['prefix' => '/baskets','middleware' => ['json.response']], function () {

    Route::resource('/',BasketController::class, ['only' => ['index','store']]);
    Route::get('/{basket_id}', [BasketController::class, 'show']);
    Route::delete('/{basket_id}', [BasketController::class, 'destroy']);
    Route::post('/{basket_id}', [BasketController::class, 'addProduct']);
    Route::patch('/{basket_id}/products/{product_id}', [BasketController::class, 'updateProduct']);
    Route::delete('/{basket_id}/products/{product_id}', [BasketController::class, 'removeProduct']);

    Route::fallback(function() {
        abort(404, 'API resource not found');
    });
});


