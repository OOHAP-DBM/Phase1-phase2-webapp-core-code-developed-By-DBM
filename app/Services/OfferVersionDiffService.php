<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Enquiries\Mail\OTPEmail;
use App\Models\Offer;
use Illuminate\Support\Collection;

class OfferVersionDiffService
{
    /**
     * Walks every OfferVersion in order and diffs its hoarding set against the
     * previous version. Deliberately avoids Eloquent Collection set operations
     * (->only(), ->diff() on Collections) entirely — an earlier version of this
     * used them and silently produced empty diffs regardless of real changes,
     * because Eloquent Collection's ->only()/->diff() behave unpredictably when
     * mixed with plain-scalar key collections. Plain array_diff/array_intersect
     * on int keys has no such ambiguity.
     */
    public function build(Offer $offer): array
    {
        $versions = $offer->versions()
            ->with('items.hoarding.doohScreen')
            ->orderBy('version_number')
            ->get();

        $diffs = [];
        $previousByHoardingId = []; // int hoarding_id => OfferVersionItem

        foreach ($versions as $version) {
            $currentByHoardingId = [];
            foreach ($version->items as $item) {
                $currentByHoardingId[(int) $item->hoarding_id] = $item;
            }

            $currentIds  = array_keys($currentByHoardingId);
            $previousIds = array_keys($previousByHoardingId);

            $addedIds   = array_values(array_diff($currentIds, $previousIds));
            $removedIds = array_values(array_diff($previousIds, $currentIds));
            $commonIds  = array_values(array_intersect($currentIds, $previousIds));

            $changedIds = [];
            $unchangedIds = [];

            foreach ($commonIds as $id) {
                $cur  = $currentByHoardingId[$id];
                $prev = $previousByHoardingId[$id];

                $curStart  = optional($cur->start_date)->format('Y-m-d');
                $prevStart = optional($prev->start_date)->format('Y-m-d');
                $curEnd    = optional($cur->end_date)->format('Y-m-d');
                $prevEnd   = optional($prev->end_date)->format('Y-m-d');
                $curPrice  = round((float) $cur->final_price, 2);
                $prevPrice = round((float) $prev->final_price, 2);

                if ($curStart !== $prevStart || $curEnd !== $prevEnd || $curPrice !== $prevPrice) {
                    $changedIds[] = $id;
                } else {
                    $unchangedIds[] = $id;
                }
            }

            $added   = array_map(fn ($id) => $currentByHoardingId[$id], $addedIds);
            $removed = array_map(fn ($id) => $previousByHoardingId[$id], $removedIds);
            $changed = array_map(fn ($id) => [
                'current'  => $currentByHoardingId[$id],
                'previous' => $previousByHoardingId[$id],
            ], $changedIds);
            $unchanged = array_map(fn ($id) => $currentByHoardingId[$id], $unchangedIds);

            $diffs[] = [
                'version'         => $version,
                'is_initial'      => $version->version_number === 1,
                'actor_type'      => $version->created_by_type,
                'added'           => $added,
                'removed'         => $removed,
                'changed'         => $changed,
                'unchanged'       => $unchanged,
                'total_amount'    => $version->total_amount,
                'item_count'      => count($currentByHoardingId),
                'has_any_change'  => !empty($added) || !empty($removed) || !empty($changed),
            ];

            $previousByHoardingId = $currentByHoardingId;
        }

        return $diffs;
    }
}



