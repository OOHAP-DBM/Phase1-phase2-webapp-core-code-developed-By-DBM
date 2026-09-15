<?php

namespace App\Http\Resources\Swagger;

/**
 * @OA\Schema(
 *     schema="OfferVersionItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=32),
 *     @OA\Property(property="hoarding_id", type="integer", example=795),
 *     @OA\Property(property="enquiry_item_id", type="integer", nullable=true, example=246),
 *     @OA\Property(property="hoarding_type", type="string", enum={"ooh","dooh"}, example="ooh"),
 *     @OA\Property(property="title", type="string", example="Bus-shelter in Lalbagh, Faizabad"),
 *     @OA\Property(property="city", type="string", example="Faizabad"),
 *     @OA\Property(property="address", type="string", nullable=true),
 *     @OA\Property(property="total_slots_per_day", type="integer", nullable=true, example=300),
 *     @OA\Property(property="start_date", type="string", format="date", example="2026-08-01"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2026-08-30"),
 *     @OA\Property(property="duration_months", type="integer", example=1),
 *     @OA\Property(property="unit_price", type="number", format="float", example=66000),
 *     @OA\Property(property="discount_amount", type="number", format="float", example=0),
 *     @OA\Property(property="final_price", type="number", format="float", example=66000),
 *     @OA\Property(property="source", type="string", enum={"enquiry","added"}, example="enquiry")
 * )
 *
 * @OA\Schema(
 *     schema="OfferVersion",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=24),
 *     @OA\Property(property="version_number", type="integer", example=3),
 *     @OA\Property(property="created_by_type", type="string", enum={"vendor","customer","admin","system"}, example="vendor"),
 *     @OA\Property(property="status", type="string", enum={"draft","sent","accepted","rejected"}, example="draft"),
 *     @OA\Property(property="subtotal", type="number", format="float", example=66000),
 *     @OA\Property(property="discount_amount", type="number", format="float", example=0),
 *     @OA\Property(property="tax_amount", type="number", format="float", example=0),
 *     @OA\Property(property="total_amount", type="number", format="float", example=66000),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OfferVersionItem"))
 * )
 *
 * @OA\Schema(
 *     schema="Offer",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=24),
 *     @OA\Property(property="offer_number", type="string", example="OFR-6A708832E1C66"),
 *     @OA\Property(property="enquiry_id", type="integer", example=246),
 *     @OA\Property(property="version", type="integer", example=3),
 *     @OA\Property(property="status", type="string", enum={"draft","sent","accepted","rejected","expired","cancelled"}, example="sent"),
 *     @OA\Property(property="price", type="number", format="float", example=5510000),
 *     @OA\Property(property="price_type", type="string", enum={"total","monthly","weekly","daily"}, example="total"),
 *     @OA\Property(property="formatted_price", type="string", example="₹55,10,000.00 (Total)"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="modification_notes", type="string", nullable=true),
 *     @OA\Property(property="valid_until", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="expiry_label", type="string", example="Expires on Aug 11, 2026"),
 *     @OA\Property(property="days_remaining", type="integer", nullable=true, example=7),
 *     @OA\Property(property="can_accept", type="boolean"),
 *     @OA\Property(property="is_archived", type="boolean"),
 *     @OA\Property(property="was_last_modified_by_vendor", type="boolean"),
 *     @OA\Property(property="was_last_modified_by_customer", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="sent_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="accepted_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="rejected_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="vendor", type="object", nullable=true,
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string"),
 *         @OA\Property(property="phone", type="string")
 *     ),
 *     @OA\Property(property="customer", type="object", nullable=true,
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string"),
 *         @OA\Property(property="phone", type="string")
 *     ),
 *     @OA\Property(property="current_version", ref="#/components/schemas/OfferVersion")
 * )
 *
 * @OA\Schema(
 *     schema="OfferListItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=24),
 *     @OA\Property(property="offer_number", type="string", example="OFR-6A708832E1C66"),
 *     @OA\Property(property="version", type="integer", example=3),
 *     @OA\Property(property="status", type="string", example="sent"),
 *     @OA\Property(property="price", type="number", format="float", example=5510000),
 *     @OA\Property(property="valid_until", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="days_remaining", type="integer", nullable=true),
 *     @OA\Property(property="hoarding_count", type="integer", example=3),
 *     @OA\Property(property="location_count", type="integer", example=2),
 *     @OA\Property(property="cities", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="has_pending_modification_request", type="boolean"),
 *     @OA\Property(property="was_last_modified_by_customer", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="customer", type="object", nullable=true),
 *     @OA\Property(property="vendor", type="object", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="OfferItemInput",
 *     type="object",
 *     required={"hoarding_id","hoarding_type","start_date","end_date","unit_price","final_price"},
 *     @OA\Property(property="hoarding_id", type="integer", example=795),
 *     @OA\Property(property="enquiry_item_id", type="integer", nullable=true, example=246),
 *     @OA\Property(property="hoarding_type", type="string", enum={"ooh","dooh"}, example="ooh"),
 *     @OA\Property(property="start_date", type="string", format="date", example="2026-08-01"),
 *     @OA\Property(property="end_date", type="string", format="date", example="2026-08-30"),
 *     @OA\Property(property="unit_price", type="number", format="float", example=66000),
 *     @OA\Property(property="discount_amount", type="number", format="float", example=0),
 *     @OA\Property(property="final_price", type="number", format="float", example=66000)
 * )
 *
 * @OA\Schema(
 *     schema="OfferStoreRequest",
 *     type="object",
 *     required={"enquiry_id","items"},
 *     @OA\Property(property="enquiry_id", type="integer", example=246),
 *     @OA\Property(property="offer_id", type="integer", nullable=true, description="Pass to add a new version to an existing offer instead of creating a new one"),
 *     @OA\Property(property="price_type", type="string", enum={"total","monthly","weekly","daily"}, nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true, maxLength=1000),
 *     @OA\Property(property="valid_until", type="string", format="date", nullable=true),
 *     @OA\Property(property="send_email", type="boolean"),
 *     @OA\Property(property="send_whatsapp", type="boolean"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OfferItemInput"))
 * )
 *
 * @OA\Schema(
 *     schema="OfferModifyRequest",
 *     type="object",
 *     required={"items"},
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OfferItemInput"))
 * )
 *
 * @OA\Schema(
 *     schema="UnavailableHoarding",
 *     type="object",
 *     @OA\Property(property="hoarding_id", type="integer"),
 *     @OA\Property(property="hoarding_name", type="string"),
 *     @OA\Property(property="reasons", type="array", @OA\Items(type="string", example="booked"))
 * )
 *
 * @OA\Schema(
 *     schema="OfferVersionDiff",
 *     type="object",
 *     @OA\Property(property="version_number", type="integer"),
 *     @OA\Property(property="actor_type", type="string", enum={"vendor","customer","admin","system"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="total_amount", type="number", format="float"),
 *     @OA\Property(property="item_count", type="integer"),
 *     @OA\Property(property="is_initial", type="boolean"),
 *     @OA\Property(property="has_any_change", type="boolean"),
 *     @OA\Property(property="added", type="array", @OA\Items(ref="#/components/schemas/OfferVersionItem")),
 *     @OA\Property(property="removed", type="array", @OA\Items(ref="#/components/schemas/OfferVersionItem")),
 *     @OA\Property(property="changed", type="array", @OA\Items(
 *         type="object",
 *         @OA\Property(property="current", ref="#/components/schemas/OfferVersionItem"),
 *         @OA\Property(property="previous", ref="#/components/schemas/OfferVersionItem")
 *     )),
 *     @OA\Property(property="unchanged", type="array", @OA\Items(ref="#/components/schemas/OfferVersionItem"))
 * )
 */
class OfferSchemas
{
    // This class holds no logic — it exists purely as an anchor for the
    // @OA\Schema annotations above, so l5-swagger:generate picks them up
    // without needing them attached to a real model or resource class.
}
