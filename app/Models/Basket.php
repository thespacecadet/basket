<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Basket extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the basket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The products that belong to the basket.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    /**
     * Get Basket Sum
     */
    public function getBasketSum(): Int
    {
        return $this->products()->sum(DB::raw('price * quantity'));
    }

    /**
     * Check if basket has products
     */
    public function hasProducts(): bool
    {

        return (bool) $this->products()->first();
    }



}
