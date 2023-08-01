<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{

    public function __construct(
        protected ApiService $apiService
    ) {
         $this->apiService = $apiService;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate)
    {
        // TODO: Complete this method

            // Create a new user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE
        ]);
      
        if($user->type == User::TYPE_MERCHANT){
            throw new \Exception('email is in use with merchant.');
            return;
        }
        $discount_code=$this->apiService->createDiscountCode($merchant);

         // Create a new affiliate record associated with the merchant
        $affiliate = new Affiliate([
            'merchant_id' => $merchant->id,
            'user_id' => $user->id,
            'commission_rate' => $commissionRate,
            'discount_code'=>$discount_code['code']
        ]);

        // Save the affiliate to the database
        $affiliate->save();

        return $affiliate;
    }
}
