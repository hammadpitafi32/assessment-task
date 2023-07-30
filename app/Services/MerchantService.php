<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {

        // TODO: Complete this method
        $data['type']=User::TYPE_MERCHANT,
        $user=User::create($data);

        // Create a new merchant associated with the user
        $merchantData = [
            'user_id' => $user->id,
            'domain' => $data['domain'],
            'display_name' => $user->name,
       
            // Include any other merchant-specific data you need to store
        ];

        return $merchant = Merchant::firstOrCreate(['domain' => $data['domain']], $merchantData);
    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        // TODO: Complete this method

        // Find the associated merchant for the user
        $merchant = $user->merchant;

         // Check if the merchant exists for the user
        if (!$merchant) {
            // Handle the case when the merchant does not exist for the user
            // For example, throw an exception or log an error
            throw new \Exception('Merchant not found for the user.');
        }

        $merchant->domain=$data['domain'];
        $merchant->display_name=$data['display_name'];
        $merchant->turn_customers_into_affiliates=$data['turn_customers_into_affiliates'];
        $merchant->default_commission_rate=$data['default_commission_rate'];
        // Add more fields as needed based on your merchant model

        // Save the updated merchant data
        $merchant->save();

        return $merchant;
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        // TODO: Complete this method
         // Use Eloquent query builder to find the merchant by email
        $merchant = Merchant::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();

        // If no merchant is found, you can return null or handle the case as needed
        return $merchant;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method

        // Find all unpaid orders for the given affiliate
        $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
            ->where('payout_status', Order::STATUS_UNPAID)
            ->get();

        // Dispatch the PayoutOrderJob for each unpaid order
        foreach ($unpaidOrders as $order) {
            // We can pass any additional data needed by the job in the second parameter
            dispatch(new PayoutOrderJob($order));
        }
    }
}
