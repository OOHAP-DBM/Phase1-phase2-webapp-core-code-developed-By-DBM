<?php
// app/Http/Resources/OfferVersionResource.php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class OfferVersionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'version_number'   => $this->version_number,
            'created_by_type'  => $this->created_by_type,
            'status'           => $this->status,
            'subtotal'         => (float) $this->subtotal,
            'discount_amount'  => (float) $this->discount_amount,
            'tax_amount'       => (float) $this->tax_amount,
            'total_amount'     => (float) $this->total_amount,
            'created_at'       => $this->created_at->toIso8601String(),
            'items'            => OfferVersionItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
