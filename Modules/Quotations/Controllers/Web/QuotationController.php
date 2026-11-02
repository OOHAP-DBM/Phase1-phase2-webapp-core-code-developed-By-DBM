<?php

namespace Modules\Quotations\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use Illuminate\Http\Request;
use App\Models\Offer;
class QuotationController extends Controller
{

    public function index(Request $request)
    {
        // Fetch quotations for the logged-in customer, optionally filter by status
        $query = Quotation::where('customer_id', auth()->id())
            ->with(['offer.enquiry', 'vendor']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $quotations = $query->orderByDesc('created_at')->paginate(10);
        return view('customer.quotations.index', compact('quotations'));
    }


    public function create(Request $request)
    {
        $offer = Offer::with([
            'customer',
            'items',
            'items.hoarding'
        ])->findOrFail($request->offer_id);

        return view(
            'customer.quotations.create',
            compact('offer')
        );
    }

    public function show($id)
    {

        $quotation = null;
        return view('customer.quotations.show', compact('quotation'));
    }
}
