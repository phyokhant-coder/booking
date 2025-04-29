<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillingAddressResource extends JsonResource
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
            'user_id' => $this->user_id,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'full_address' => $this->full_address,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'user' => $this->whenLoaded('user',
                fn() => new UserResource($this->user),
                ''),
            'country' => $this->whenLoaded('country',
                fn() => new CountryResource($this->country),
                ''),
            'state' => $this->whenLoaded('state',
                fn() => new StateResource($this->state),
                ''),
        ];
    }
}
