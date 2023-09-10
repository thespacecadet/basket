<?php

namespace App\Http\Controllers;

use App\Http\Resources\BasketResource;
use App\Models\Basket;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class BasketController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/baskets",
     *     tags={"Shopping Basket"},
     *     summary="Get all baskets",
     *     @OA\Response(response="200",
     *      description="OK"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $baskets = Basket::all();
        return response()->json(['message' => 'OK', 'status' => 200, 'content' => $baskets])->setStatusCode(200);
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
    public function show($basket_id): JsonResponse
    {
        $basket = Basket::with(['products:id,price'])
            ->findOrFail($basket_id);
        $basket->basketSum = $basket->getBasketSum();
        return response()->json(['message' => 'OK', 'status' => 200, 'content' => $basket])->setStatusCode(200);
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
    public function store(Request $request): JsonResponse
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'user_id' => 'required | integer'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first(), 'status' => 400])->setStatusCode(400);
        }

        // find if the user exists
        try {
            User::findOrFail($request->user_id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found', 'status' => 404])->setStatusCode(404);
        }

        // add basket to the user
        $basket = new Basket();
        $basket->user_id = $request->user_id;
        $basket->save();
        return response()->json(['message' => 'OK', 'status' => 200])->setStatusCode(200);
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
     *      description="Basket not found")
     * )
     */
    public function destroy(Request $request): JsonResponse
    {
        $basketId = $request->basket_id;
        // check if the basket exists
        try {
            $basket = Basket::findOrFail($basketId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Basket not found', 'status' => 404])->setStatusCode(404);
        }
        $basket->delete();
        return response()->json(['message' => 'OK', 'status' => 200])->setStatusCode(200);
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
     *     @OA\Response(response="404",
     *      description="Product not found in basket"),
     *      @OA\Response(response="400",
     *      description="Resource already exists")
     * )
     */

    public function updateProduct(Request $request): JsonResponse
    {
        //validate request
        $validator = Validator::make($request->all(), [
            'quantity' => 'required | integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first(), 'status' => 400])->setStatusCode(400);
        }

        $productId = $request->product_id;
        $quantity = $request->quantity;

        // find basket, return 404 if failed
        $basket = Basket::findOrFail($request->basket_id);

        // Update existing entry in the pivot table. return 404 if it currently does not exist
        if ($basket->products()->updateExistingPivot($productId, ['quantity' => $quantity])) {
            return response()->json(['message' => 'OK', 'status' => 200])->setStatusCode(200);
        } else {
            return response()->json(['message' => 'Product not found in basket', 'status' => 404])->setStatusCode(404);
        }
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
            return response()->json(['error' => $validator->errors()->first(), 'status' => 400])->setStatusCode(400);
        }

        $basket = Basket::findOrFail($request->basket_id);
        $productId = $request->product_id;
        $product = Product::findOrFail($request->product_id);
        $quantity = $request->quantity ? $request->quantity : 1;
        $basket->products()->attach($productId, ['quantity' => $quantity]);
        return response()->json(['message' => 'OK', 'status' => 200])->setStatusCode(200);
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

    public function removeProduct(Request $request): JsonResponse
    {
        $basketId = $request->basket_id;
        $basket = Basket::findOrFail($basketId);
        $productId = $request->product_id;
        $success = $basket->products()->detach($productId);
        if (!$success) {
            return response()->json(['message' => 'Resource not found', 'status' => 404])->setStatusCode(404);
        }
        return response()->json(['message' => 'OK', 'status' => 200])->setStatusCode(200);
    }

    /**
     * @OA\Get(
     *     path="/api/baskets/users/user_id}",
     *     tags={"Shopping Basket"},
     *     summary="Get basket by user id",
     *     @OA\Parameter(
     *         description="User Id",
     *         in="path",
     *         name="user_id",
     *         required=true
     *     ),
     *     @OA\Response(response="200",
     *      description="OK"),
     *      @OA\Response(response="404",
     *      description="Resource not Found")
     * )
     */

    public function getBasketByUser(Request $request): JsonResponse
    {
        $userId = $request->user_id;
        $user = User::findOrFail($userId);
        $basket = $user->basket()->first();
        $basketData = new BasketResource($basket);
        return response()->json(['message' => 'OK', 'status' => 200, 'content' => $basket])->setStatusCode('200');
    }
}
