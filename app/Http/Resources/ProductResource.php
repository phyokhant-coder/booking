<?php

namespace App\Http\Resources;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\UrlGenerator;

class ProductResource extends JsonResource
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
            'product_code' => $this->product_code,
            'product_category' => $this->whenLoaded('productCategory',
                fn() => new ProductCategoryResource($this->productCategory),
                ''),
            'product_brand' => $this->whenLoaded('productBrand',
                fn() => new ProductBrandResource($this->productBrand),
                ''),
            'name' => $this->name,
            'image_url' => $this->getImageUrl(),
            'product_images' => $this->whenLoaded('productImages', function () {
                return $this->productImages->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => $image->image_url,
                    ];
                });
            }, ''),
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $this->description ?? '',
            'status' => $this->status,
            'product_variants' => $this->whenLoaded('productVariants',
                fn() => ProductVariantResource::collection($this->productVariants),
                ''),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : '',
        ];
    }

    public function getImageUrl(): Application|string|UrlGenerator
    {
        if ($this->image_url) {
            return asset("storage/products/{$this->image_url}");
        }

        return '';
    }
}
