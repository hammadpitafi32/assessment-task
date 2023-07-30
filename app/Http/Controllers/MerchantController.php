<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MerchantController extends Controller
{
    protected $merchantService;
    public function __construct(
        MerchantService $merchantService
    ) {
        $this->merchantService = $merchantService;
    }

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        // Get the 'from_date' and 'to_date' from the request
        $fromDate = Carbon::parse($request->input('from_date'));
        $toDate = Carbon::parse($request->input('to_date'));

        // Get the authenticated merchant
        $merchant = Merchant::find(auth()->user()->id);

        // Calculate the total number of orders in the date range
        $count = Order::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->count();

        // Calculate the sum of order subtotals in the date range (revenue)
        $revenue = Order::where('merchant_id', $merchant->id)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('subtotal');

        // Calculate the amount of unpaid commissions for orders with an affiliate
        $commissionOwed = Order::where('merchant_id', $merchant->id)
            ->where('payout_status', 'unpaid')
            ->whereHas('affiliate')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum(function ($order) {
                // Calculate the unpaid commission for each order with an affiliate
                return $order->subtotal * $order->affiliate->commission_rate / 100;
            });

        return response()->json([
            'count' => $count,
            'commission_owed' => $commissionOwed,
            'revenue' => $revenue,
        ]);
    }

    public function register(Request $request)
    {
        // Validate the incoming registration data (you can add more validation rules)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'api_key' => 'required|string',
            'domain' => 'required|string',
        ]);

        // Set data for a new user
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('api_key')), // Hash the API key (password field)
            'domain' => $request->input('domain'),
           
        ];

 
        $merchant = $this->merchantService->register($data);

        // Return the merchant data in JSON format
        return response()->json([
            'merchant' => $merchant
        ]);
    }
}
