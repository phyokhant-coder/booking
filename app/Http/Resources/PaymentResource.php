<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'payment_method_id' => $this->payment_method_id,
            'screenshot_image_url' => $this->getImageUrl(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'payment_method' => $this->whenLoaded('paymentMethod',
                fn() => new CountryResource($this->paymentMethod),
            ''),
        ];
    }
    public function getImageUrl(): Application|string|UrlGenerator
    {
        if ($this->screenshot_image_url) {
            return asset("storage/payments/{$this->screenshot_image_url}");
        }

        return '';
    }
}
