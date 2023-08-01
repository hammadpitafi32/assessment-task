<?php

namespace App\Services;

use Illuminate\Validation\Validator;
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
         // Create a new user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT
        ]);

        // Create a new merchant associated with the user
        return Merchant::create([
            'domain' => $data['domain'],
            'user_id' => $user->id,
            'display_name' => $data['name']
        ]);

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
            // throw new \Exception('Merchant not found for the user.');
            return;
        }

        $merchant->domain=$data['domain'];
        $merchant->display_name=$data['name'];
     
        // Save the updated merchant data
        $merchant->save();
      
        $user->email=$data['email'];
        $user->name=$data['name'];
        $user->password=$data['api_key'];
        $user->save();

        return;
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
        try{
            // Find all unpaid orders for the given affiliate
            $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
                ->where('payout_status', Order::STATUS_UNPAID)
                ->get();

            // Dispatch the PayoutOrderJob for each unpaid order
            foreach ($unpaidOrders as $order) {
                // We can pass any additional data needed by the job in the second parameter
                dispatch(new PayoutOrderJob($order));
            }
        }catch (\Exception $e) {
            throw new \Exception('roll back job.');
        }
       
    }
}
