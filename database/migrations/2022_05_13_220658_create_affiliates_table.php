<?php

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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('merchant_id');
            // TODO: Replace me with a brief explanation of why floats aren't the correct data type, and replace with the correct data type.

            //Hammad: floats not recommended for storing currency or monetary values. The main reason is that floating-point numbers can lead to inaccuracies in calculations due to the way they are represented in binary format.

            // Instead, it's better to use a precise data type that can accurately store monetary values without any loss of precision. In the context of currency values, it's common to use the DECIMAL data type.
            
            $table->decimal('commission_rate', 10, 2);
            $table->string('discount_code');
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
        Schema::dropIfExists('affiliates');
    }
};
