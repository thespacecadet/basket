<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The Baskets that belong to the product.
     */
    public function baskets(): BelongsToMany
    {
        return $this->belongsToMany(Basket::class);
    }
}
