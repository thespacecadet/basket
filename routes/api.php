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
//    dd('bla');
    Route::get('/', [BasketController::class, 'index']);
    Route::post('/', [BasketController::class, 'store']);
    Route::get('/{basket_id}', [BasketController::class, 'show']);
//Route::post('/baskets/user/{user_id}',[BasketController::class,'store']);
    Route::delete('/{basket_id}', [BasketController::class, 'destroy']);
    Route::fallback(function() {
        return 'Hm, why did you land here somehow?';
    });
});


