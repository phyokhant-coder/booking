<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
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
            'product_id' => $this->product_id,
            'color' => $this->color,
            'image_url' =>  $this->getImageUrl(),
            'product_variant_details' => $this->whenLoaded('productVariantDetails',
                fn() => ProductVariantDetailResource::collection($this->productVariantDetails),
                ''),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    public function getImageUrl(): Application|string|UrlGenerator
    {
        if ($this->image_url) {
            return asset("storage/variants/{$this->image_url}");
        }

        return '';
    }
}
