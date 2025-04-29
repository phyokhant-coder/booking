<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_no' => $this->order_no,
            'user' => $this->whenLoaded('user',
                fn() => new UserResource($this->user),
                ''),
            'billing_address' => $this->whenLoaded('billingAddress',
                fn() => new BillingAddressResource($this->billingAddress),
                ''),
            'order_lines' => $this->whenLoaded('orderLines',
                fn() => OrderLineResource::collection($this->orderLines),
                ''),
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'order_note' => $this->order_note,
            'order_date' => $this->order_date,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : '',
        ];
    }
}
