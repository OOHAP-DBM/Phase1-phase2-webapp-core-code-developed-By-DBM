<?php

namespace Modules\Enquiries\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Enquiries\Models\DirectEnquiry;
use Modules\Enquiries\Mail\VendorDirectEnquiryMail;
use Modules\Enquiries\Mail\UserDirectEnquiryConfirmation;
use Modules\Enquiries\Mail\AdminDirectEnquiryMail;
use Modules\Enquiries\Notifications\VendorDirectEnquiryNotification;
use Modules\Enquiries\Notifications\CustomerDirectEnquiryNotification;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DirectEnquiryController extends Controller
{

    /**
     * @OA\Get(
     *     path="/enquiries/direct-api/list",
     *     summary="List direct enquiries for authenticated user",
     *     description="Returns all direct enquiries for the authenticated customer or vendor. No pagination.",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="List of enquiries"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function listFor(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Determine role from authenticated user
        $role = $user->active_role ?? null;


            $query = DirectEnquiry::where('user_id', $user->id)->with('assignedVendors')->latest();


        // Optional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('location_city', 'like', "%{$search}%");
            });
        }

        // Return all results (no pagination) as requested
        $enquiries = $query->get();

        return response()->json($enquiries);
    }

    /**
     * @OA\Get(
     *     path="/enquiries/direct-api/{id}",
     *     summary="Show a single direct enquiry (authenticated)",
     *     tags={"Direct Enquiries"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer", example=123)),
     *     @OA\Response(response=200, description="Enquiry detail"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not found or access denied")
     * )
     */
    public function showFor($enquiryId, Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $role = $user->active_role ?? null;
        if ($role === 'customer') {
            $enquiry = DirectEnquiry::where('id', $enquiryId)->where('user_id', $user->id)->with('assignedVendors')->first();
        } elseif ($role === 'vendor') {
            $enquiry = DirectEnquiry::where('id', $enquiryId)->whereHas('assignedVendors', function ($q) use ($user) {
                $q->where('vendor_id', $user->id);
            })->with('assignedVendors')->first();
        } else {
            // admin or other roles - allow access
            $enquiry = DirectEnquiry::where('id', $enquiryId)->with('assignedVendors')->first();
        }

        if (!$enquiry) {
            return response()->json(['success' => false, 'message' => 'Enquiry not found or access denied'], 404);
        }

        return response()->json(['success' => true, 'enquiry' => $enquiry]);
    }

    /**
     * Helper: normalize city name (copied/trimmed for API use)
     */
    private function normalizeCityName(string $city): string
    {
        $city = trim(strtolower($city));

        $cityMappings = [
            'lucknow' => ['lucknow', 'lko', 'lakhnau', 'lakhnaow', 'lucknaw', 'lukhnow'],
            'kanpur' => ['kanpur', 'cawnpore', 'kanpoor', 'kanpore'],
            'varanasi' => ['varanasi', 'banaras', 'benares', 'kashi', 'varnasi'],
            'mumbai' => ['mumbai', 'bombay', 'mumbay', 'mumby', 'mombai'],
            'delhi' => ['delhi', 'dilli', 'dehli', 'new delhi', 'newdelhi'],
            'bangalore' => ['bangalore', 'bengaluru', 'bangaluru', 'banglore', 'bengaloor'],
            'kolkata' => ['kolkata', 'calcutta', 'kolkatta', 'kalkatta', 'kolkota'],
            'hyderabad' => ['hyderabad', 'hydrabad', 'haidarabad', 'hyderabadh'],
            'chennai' => ['chennai', 'madras', 'chenai', 'chenna'],
            'pune' => ['pune', 'poona', 'puna'],
            'ahmedabad' => ['ahmedabad', 'amdavad', 'ahmadabad', 'ahmdabad'],
            'jaipur' => ['jaipur', 'jaypur', 'jeypore', 'jeypur'],
            'surat' => ['surat', 'surath', 'suratt'],
            'indore' => ['indore', 'indor', 'indaur'],
            'bhopal' => ['bhopal', 'bhopl', 'bhopaal'],
        ];

        foreach ($cityMappings as $standard => $variations) {
            if (in_array($city, $variations)) return ucwords($standard);
        }

        foreach ($cityMappings as $standard => $variations) {
            foreach ($variations as $variation) {
                if (levenshtein($city, $variation) <= 2) return ucwords($standard);
            }
        }

        return ucwords($city);
    }

    private function normalizeLocalityName(string $locality, string $city): string
    {
        if ($locality === 'To be discussed') return $locality;
        $locality = trim(strtolower($locality));
        $city = strtolower($city);

        $localityMappings = [
            'lucknow' => [
                'hazratganj' => ['hazratganj', 'hazrat ganj', 'hazratganj', 'ganj'],
                'gomti nagar' => ['gomti nagar', 'gomtinagar', 'gomti', 'gomati nagar'],
                'indira nagar' => ['indira nagar', 'indiranagar', 'indra nagar'],
            ],
            'mohali' => [
                'sector 70' => ['sector 70', 'sec 70', 'sector-70'],
            ],
        ];

        if (isset($localityMappings[$city])) {
            foreach ($localityMappings[$city] as $standard => $variations) {
                if (in_array($locality, $variations)) return ucwords($standard);
                foreach ($variations as $variation) {
                    if (levenshtein($locality, $variation) <= 2) return ucwords($standard);
                }
            }
        }

        return ucwords($locality);
    }

    private function findRelevantVendors(string $city, array $localities, array $hoardingTypes)
    {
        $hoardingTypes = array_map('strtolower', $hoardingTypes);

        $hoardingQuery = DB::table('hoardings')->select('vendor_id')->where('status', 'active')->whereNotNull('vendor_id');

        $columns = ['city', 'state', 'locality'];
        $hoardingQuery->where(function ($q) use ($city, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$city}%")->orWhere($column, 'like', $this->getFuzzyPattern($city));
            }
        });

        if (!empty($localities) && $localities[0] !== 'To be discussed') {
            $hoardingQuery->where(function ($q) use ($localities) {
                foreach ($localities as $locality) {
                    $q->orWhere('locality', 'like', "%{$locality}%")->orWhere('address', 'like', "%{$locality}%")->orWhere('landmark', 'like', "%{$locality}%");
                }
            });
        }

        $hoardingQuery->where(function ($q) use ($hoardingTypes) {
            foreach ($hoardingTypes as $type) {
                $q->orWhere('hoarding_type', 'like', "%{$type}%");
            }
        });

        $vendorIds = $hoardingQuery->distinct()->pluck('vendor_id')->filter()->unique()->toArray();

        if (empty($vendorIds)) {
            $vendorIds = DB::table('hoardings')->select('vendor_id')->where('status', 'active')->where('city', 'like', "%{$city}%")->whereNotNull('vendor_id')->distinct()->pluck('vendor_id')->toArray();
        }

        $vendors = User::whereIn('id', $vendorIds)->where('active_role', 'vendor')->where('status', 'active')->whereNotNull('email')->get();

        return $vendors;
    }

    private function getFuzzyPattern(string $text): string
    {
        return '%' . implode('%', str_split(strtolower($text))) . '%';
    }
}
