<?php
// app/Http/Resources/OfferVersionItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferVersionItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'hoarding_id'     => $this->hoarding_id,
            'enquiry_item_id' => $this->enquiry_item_id,
            'hoarding_type'   => $this->hoarding_type,
            'title'           => $this->hoarding?->title,
            'city'            => $this->hoarding?->city,
            'address'         => $this->hoarding?->address,
            'total_slots_per_day' => $this->hoarding?->doohScreen?->total_slots_per_day,
            'start_date'      => optional($this->start_date)->format('Y-m-d'),
            'end_date'        => optional($this->end_date)->format('Y-m-d'),
            'duration_months' => $this->duration_months,
            'unit_price'      => (float) $this->unit_price,
            'discount_amount' => (float) $this->discount_amount,
            'final_price'     => (float) $this->final_price,
            'source'          => $this->meta['source'] ?? 'enquiry',
        ];
    }
}
