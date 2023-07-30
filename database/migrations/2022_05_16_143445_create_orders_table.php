<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->foreignId('affiliate_id')->nullable()->constrained();
            // TODO: Replace floats with the correct data types (very similar to affiliates table)

           //Hammad: floats not recommended for storing currency or monetary values. The main reason is that floating-point numbers can lead to inaccuracies in calculations due to the way they are represented in binary format.

            // Instead, it's better to use a precise data type that can accurately store monetary values without any loss of precision. In the context of currency values, it's common to use the DECIMAL data type.
            
            $table->decimal('subtotal', 10, 2);
            $table->decimal('commission_owed', 10, 2)->default(0.00);
            $table->string('payout_status')->default(Order::STATUS_UNPAID);
            $table->string('discount_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
