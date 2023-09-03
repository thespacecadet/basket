<?php

namespace App\Http\Controllers;

use App\Http\Resources\BasketResource;
use App\Models\Basket;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class BasketController extends Controller
{
    //
    /**
     * @OA\Get(
     *     path="/api/baskets",
     *     tags={"Shopping Basket"},
     *     summary="Get all baskets",
     *     @OA\Response(response="200",
     *      description="OK"
     *      )
     * )
     */
    public function index(): Collection
    {
        return Basket::all();
    }

    /**
     * @OA\Get(
     *     path="/api/baskets/{basket_id}",
     *     tags={"Shopping Basket"},
     *     summary="Get basket by id",
     *     @OA\Parameter(
     *         description="Basket Id",
     *         in="path",
     *         name="basket_id",
     *         required=true
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="Resource not Found")
     * )
     */
    public function show($basket_id): JsonResource
    {
        $basket = Basket::findOrFail($basket_id);
        return new BasketResource($basket);
    }

    /**
     * @OA\Post(
     *     path="/api/baskets",
     *     tags={"Shopping Basket"},
     *     summary="Add a new Basket",
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass Basket details",
     *       @OA\JsonContent(
     *          required={"user_id"},
     *          @OA\Property(property="user_id", type="int", example="3"),
     *       ),
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="User not found"),
     *      @OA\Response(response="400",
     *      description="Resource already exists")
     * )
     */
    public function store(Request $request)
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required | integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()])->setStatusCode(400);
        }

        try {
            User::findOrFail($request->user_id);
        }
        catch (ModelNotFoundException $e){
            return response()->json(['message' => 'User not found'])->setStatusCode(404);
        }

        $basket = new Basket();
        $basket->user_id = $request->user_id;
        $basket->save();
        return response()->json(['Basket added'])->setStatusCode(200);
    }

    /**
     * @OA\Delete(
     *     path="/api/baskets/{basket_id}",
     *     tags={"Shopping Basket"},
     *     summary="Remove basket by id",
     *     @OA\Parameter(
     *         description="Basket Id",
     *         in="path",
     *         name="basket_id",
     *         required=true
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="Resource not Found")
     * )
     */
    public function destroy(Request $request)
    {
        $basketId = $request->basket_id;
        $basket = Basket::findOrFail($basketId);
        $basket->delete();
        return response()->json(['message' => 'Basket with id ' . $basketId . ' removed'])->setStatusCode(200);
    }
    /**
     * @OA\Patch(
     *     path="/api/baskets/{basket_id}/products/{product_id}",
     *     tags={"Shopping Basket"},
     *     summary="Update quantity of a product",
     *     @OA\Parameter(
     *         description="Basket Id",
     *         in="path",
     *         name="basket_id",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         description="Product Id",
     *         in="path",
     *         name="product_id",
     *         required=true
     *     ),
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass Product quantity",
     *       @OA\JsonContent(
     *          required={"quantity"},
     *          @OA\Property(property="quantity", type="int", example="15"),
     *       ),
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="Resource not found"),
     *      @OA\Response(response="400",
     *      description="Resource already exists")
     * )
     */

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
        return response()->json(['message' => 'Product with id ' . $productId . ' updated to basket'])->setStatusCode(200);
    }

    /**
     * @OA\Post(
     *     path="/api/baskets/{basket_id}",
     *     tags={"Shopping Basket"},
     *     summary="Add product to basket",
     *     @OA\Parameter(
     *         description="Basket Id",
     *         in="path",
     *         name="basket_id",
     *         required=true
     *     ),
     *     @OA\RequestBody(
     *     required=true,
     *     description="Pass Product details",
     *       @OA\JsonContent(
     *          required={"product_id"},
     *          @OA\Property(property="product_id", type="int", example="3",default="1"),
     *          @OA\Property(property="quantity", type="int", example="5"),
     *       ),
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="Resource not found"),
     *      @OA\Response(response="400",
     *      description="Resource already exists")
     * )
     */

    public function addProduct(Request $request): JsonResponse
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'product_id' => 'required | integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()])->setStatusCode(400);
        }

        $basket = Basket::findOrFail($request->basket_id);
        $productId = $request->product_id;
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ? $request->quantity : 1;
        $basket->products()->attach($productId, ['quantity' => $quantity]);
        return response()->json(['message' => 'Product with id ' . $productId . ' added to basket'])->setStatusCode(200);
    }

    /**
     * @OA\Delete(
     *     path="/api/baskets/{basket_id}/products/{product_id}",
     *     tags={"Shopping Basket"},
     *     summary="Remove product from basket",
     *     @OA\Parameter(
     *         description="Basket Id",
     *         in="path",
     *         name="basket_id",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         description="Product Id",
     *         in="path",
     *         name="product_id",
     *         required=true
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="Resource not found"),
     *      @OA\Response(response="400",
     *      description="Resource already exists")
     * )
     */

    public function removeProduct(Request $request)
    {
        $basketId = $request->basket_id;
        $basket = Basket::findOrFail($basketId);
        $productId = $request->product_id;
        $success = $basket->products()->detach($productId);
        if (!$success) {
            return response()->json(['message' => 'Product not found'])->setStatusCode(404);
        }
        return response()->json(['message' => 'OK'])->setStatusCode(200);
    }
}
