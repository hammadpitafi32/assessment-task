<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {
        $this->order = $order;
    }

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method

        // Perform the payout process using the ApiService
        try {
            $email = $this->order->affiliate->user->email; // Assuming the affiliate email is associated with the order
            $amount = $this->order->subtotal; // Assuming the payout amount is the subtotal of the order

            // Use the ApiService to send the payout
            $apiService->sendPayout($email, $amount);

            // If the payout is successful, update the order status to "paid"
            $this->order->payout_status = Order::STATUS_PAID;
            $this->order->save();
        } catch (\Exception $e) {
            // If there's an exception during the payout, leave the order status as "unpaid"
            // You can handle the exception here (e.g., logging, sending alerts, etc.)
            throw new \Exception('Payout process failed .');
        }
    }
}
