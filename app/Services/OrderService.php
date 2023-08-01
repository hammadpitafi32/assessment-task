<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{

    public function __construct(
        protected AffiliateService $affiliateService
    ) {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {

        // TODO: Complete this method
        $user=User::where('email',$data['customer_email'])->first();

        if(!$user){
                // throw an exception 
             return;
        }
        $merchant=Merchant::where('domain',$data['merchant_domain'])->first();

        if(!$merchant){
                // throw an exception
            return;
        }

        // Check if an affiliate exists for the given customer_email
        $affiliate = Affiliate::where('user_id', $user->id)->where('merchant_id',$merchant->id)->first();

        if(!$affiliate){
                // Create a new affiliate for the customer_email
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.0);
        }

        // Check if the order_id already exists to avoid processing duplicate orders
        $existingOrder = Order::find($data['order_id']);

        if ($existingOrder) {
           
            // Handle the case when a duplicate order is found
            // For example, throw an exception or log a warning
            return $existingOrder;
        }

        // Log the commission for the affiliate
        $commission = $data['subtotal_price'] * $affiliate->commission_rate / 100;
        // Create a new order and log any commissions
        $order = new Order([
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'subtotal' => $data['subtotal_price'],
            'discount_code' => $data['discount_code'],
            'commission_owed'=>$commission
            
        ]);

        // Save the order to the database
        $order->save();

        return;
    }
}
