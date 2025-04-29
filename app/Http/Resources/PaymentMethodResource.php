<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
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
            'name' => $this->name,
            'image_url' =>  $this->getImageUrl(),
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
    public function getImageUrl(): Application|string|UrlGenerator
    {
        if ($this->image_url) {
            return asset("storage/payment_methods/{$this->image_url}");
        }

        return '';
    }

}
