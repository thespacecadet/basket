<?php

namespace App\Http\Controllers;

use App\Http\Resources\BasketResource;
use App\Models\Basket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
}
