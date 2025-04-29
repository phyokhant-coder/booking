<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_line_no' => $this->order_line_no,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product',
                fn() => new ProductResource($this->product),
                ''),
            'product_variant_id' => $this->product_variant_id,
            'product_variant' => $this->whenLoaded('productVariant',
                fn() => new ProductVariantResource($this->productVariant),
                ''),
            'product_variant_detail' => $this->whenLoaded('productVariantDetail',
                fn() => new ProductVariantDetailResource($this->productVariantDetail),
                ''),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_price' => $this->total_price,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
