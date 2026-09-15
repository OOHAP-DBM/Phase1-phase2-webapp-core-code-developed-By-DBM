<?php
// app/Http/Resources/OfferListResource.php — lighter shape for index/listing

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'offer_number'    => $this->offer_number,
            'version'         => $this->version,
            'enquiry_id'      => $this->enquiry_id,
            'status'          => $this->status,
            'price'           => (float) $this->price,
            'valid_until'     => optional($this->valid_until)->toIso8601String(),
            'days_remaining'  => $this->getDaysRemaining(),
            'hoarding_count'  => $this->hoardingCount(),
            'location_count'  => $this->locationCount(),
            'cities'          => $this->locationCities(),
            'has_pending_modification_request' => $this->hasPendingModificationRequest(),
            'was_last_modified_by_customer'    => $this->wasLastModifiedByCustomer(),
            'created_at'      => $this->created_at->toIso8601String(),
            'updated_at'      => $this->updated_at->toIso8601String(),
            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? ['id' => $this->customer->id, 'name' => $this->customer->name] : null),
            'vendor'   => $this->whenLoaded('vendor', fn () => $this->vendor ? ['id' => $this->vendor->id, 'name' => $this->vendor->name] : null),
        ];
    }
}
