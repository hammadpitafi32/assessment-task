<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{

    public function __construct(
        protected OrderService $orderService
    ) {
        $this->orderService = $orderService;
    }

    /**
     * Pass the necessary data to the process order method
     * 
     * @param  Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        // TODO: Complete this method

        // Assuming the webhook data is available in the $request

        $data=[
            'order_id'=>$request->input('order_id'),
            'subtotal'=>$request->input('subtotal'),
            'merchant_domain'=>$request->input('merchant_domain'),
            'discount_code'=>$request->input('discount_code'),
            'customer_email'=>$request->input('customer_email'),
            'customer_name'=>$request->input('customer_name')

        ];
        // Call the processOrder method from the OrderService
        $order = $this->orderService->processOrder($data);

        // Prepare the response in JSON format
        $response = [
            'status' => 'success',
            'message' => 'Order processed successfully.',
            'order' => $order, //  $order is the response from the processOrder method
        ];

        // Return the response as a JSON response
        return response()->json($response);
    }
}
