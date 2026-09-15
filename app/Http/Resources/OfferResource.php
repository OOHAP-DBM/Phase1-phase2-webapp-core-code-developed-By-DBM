<?php
// app/Http/Resources/OfferResource.php — full detail (show endpoint)

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'offer_number'        => $this->offer_number,
            'enquiry_id'          => $this->enquiry_id,
            'version'             => $this->version,
            'status'              => $this->status,
            'price'               => (float) $this->price,
            'price_type'          => $this->price_type,
            'formatted_price'     => $this->getFormattedPrice(),
            'description'         => $this->description,
            'modification_notes'  => $this->modification_notes,
            'valid_until'         => optional($this->valid_until)->toIso8601String(),
            'expiry_label'        => $this->getExpiryLabel(),
            'days_remaining'      => $this->getDaysRemaining(),
            'can_accept'          => $this->canAccept(),
            'is_archived'         => $this->isArchived(),
            'was_last_modified_by_vendor'   => $this->wasLastModifiedByVendor(),
            'was_last_modified_by_customer' => $this->wasLastModifiedByCustomer(),
            'created_at'          => $this->created_at->toIso8601String(),
            'sent_at'             => optional($this->sent_at)->toIso8601String(),
            'accepted_at'         => optional($this->accepted_at)->toIso8601String(),
            'rejected_at'         => optional($this->rejected_at)->toIso8601String(),
            'vendor'   => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id, 'name' => $this->vendor->name,
                'email' => $this->vendor->email, 'phone' => $this->vendor->phone,
            ]),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id, 'name' => $this->customer->name,
                'email' => $this->customer->email, 'phone' => $this->customer->phone,
            ]),
            'current_version' => new OfferVersionResource($this->whenLoaded('currentVersion')),
        ];
    }
}
