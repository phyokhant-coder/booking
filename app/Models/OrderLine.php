<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderLine extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_line_no',
        'order_id',
        'product_id',
        'product_variant_id',
        'product_variant_detail_id',
        'quantity',
        'price',
        'total_price'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    /**
     * Product Model Linked.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Product Model Linked.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Product Variant Model Linked.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productVariantDetail(): BelongsTo
    {
        return $this->belongsTo(ProductVariantDetail::class);
    }

}
