<?php

namespace App\Http\Controllers;

use App\Http\Resources\BasketResource;
use App\Models\Basket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

class BasketController extends Controller
{
    //
    public function index(): Collection
    {
        return Basket::all();
    }

    public function show($basket_id): JsonResource
    {
        $basket = Basket::findOrFail($basket_id);
        return new BasketResource($basket);
    }

    public function store(Request $request){
        $basket = new Basket();
        $basket->user_id = $request->user_id;
        return 'worked '. $basket->save();
    }

    public function destroy(Request $request){
            $basket = Basket::findOrFail($request->basket_id);

        return  $basket->delete();
    }

    public function updateProduct(Request $request)
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'quantity' => 'required | integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()])->setStatusCode(400);
        }

        //update product
        $productId = $request->product_id;
        $quantity = $request->quantity;
        $basket = Basket::findOrFail($request->basket_id);
        $basket->products()->sync([
            $productId => [
                'quantity' => $quantity
            ]]);
        return response()->json(['message' =>'Product with id '.$productId.' updated to basket'])->setStatusCode(200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function addProduct(Request $request): JsonResource
    {
        $basket = Basket::findOrFail($request->basket_id);
        $productId = $request->product_id;
        $quantity = $request->quantity ? $request->quantity : 1 ;
        $basket->products()->attach($productId,['quantity' => $quantity]);
        return response()->json(['message' =>'Product with id '.$productId.' added to basket'])->setStatusCode(200);
    }
    public function removeProduct(Request $request)
    {
        $basketId = $request->basket_id;
        $basket = Basket::findOrFail($basketId);
        $productId = $request->product_id;
        $success = $basket->products()->detach($productId);
        if(!$success){
            return response()->json(['message' =>'Product with id '.$productId.' Is not in Basket'])->setStatusCode(400);
        }
        return response()->json(['message' =>'Product with id '.$productId.' removed from basket with id ' . $basketId])->setStatusCode(200);
    }
}
