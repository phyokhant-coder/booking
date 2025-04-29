<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user ?? '')),
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product ?? '')),
            'product_variant' => $this->whenLoaded('productVariant', fn () => new ProductVariantResource($this->productVariant ?? '')),
            'product_variant_detail' => $this->whenLoaded('productVariantDetail', function () {
                return $this->productVariantDetail ? [
                    'id' => $this->productVariantDetail->id ?? '',
                    'size' => $this->productVariantDetail->size ?? '',
                    'price' => $this->productVariantDetail->price ?? '',
                    'quantity' => $this->productVariantDetail->quantity ?? '',
                ] : '';
            }),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'deleted_at' => $this->deleted_at ? $this->updated_at->toDateTimeString() : '',
        ];
    }
}
