<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorKyc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class VendorOnboardingService
{
    /**
     * Save business, bank info, and PAN document for vendor onboarding.
     * All operations are transactional for safety.
     *
     * @param User $user
     * @param array $data
     * @return VendorProfile
     * @throws Exception
     */
    public function saveBusinessInfo(User $user, array $data): VendorProfile
    {
        return DB::transaction(function () use ($user, $data) {

            // Create vendor profile if it does not exist
            $profile = $user->vendorProfile;

            if (!$profile) {
                $profile = new VendorProfile([
                    'user_id' => $user->id,
                    'onboarding_status' => 'draft',
                    'onboarding_step' => 1,
                ]);
            }

            // IMPORTANT:
            // Do NOT overwrite onboarding_status here.
            // If registration already auto-approved the vendor,
            // keep "approved".
            //
            // Existing statuses such as:
            // approved
            // pending_approval
            // rejected
            // suspended
            // draft
            // will remain unchanged.

            $profile->fill([
                'gstin' => $data['gstin'] ?? null,
                'company_type' => $data['business_type'] ?? null,
                'company_name' => $data['business_name'] ?? null,
                'registered_address' => $data['registered_address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'pincode' => $data['pincode'] ?? null,

                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'account_holder_name' => $data['account_holder_name'] ?? null,

                'pan' => $data['pan_number'] ?? null,
            ]);

            // Handle PAN document upload
            if (
                isset($data['pan_card_document']) &&
                $data['pan_card_document'] instanceof UploadedFile
            ) {
                $profile->pan_card_document = $this->storePanDocument(
                    $user,
                    $data['pan_card_document']
                );
            }

            // Onboarding step tracking
            $profile->onboarding_step = max(
                1,
                (int) $profile->onboarding_step
            );

            $profile->save();

            return $profile;
        });
    }

    /**
     * Store PAN document securely and return storage path.
     */
    protected function storePanDocument(User $user, UploadedFile $file): string
    {
        $shard = sprintf('%02d/%02d', floor($user->id / 100), $user->id % 100);
        $path = "media/vendors/documents/{$shard}";
        $filename = 'pan_' . time() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $filename);
    }
}
