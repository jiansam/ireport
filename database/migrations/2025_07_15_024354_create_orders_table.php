<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->string('id', 45)->primary()->comment('訂單編號');
            
            $table->string('member_id', 45)->comment('會員編號');
            
            $table->string('status', 45)->comment('已付款、已授權、授權失敗、逾期未付、付款失敗');
            $table->integer('price')->comment('付費金額');
            
            $table->string('name', 45)->comment('購買人姓名');
            $table->string('phone', 45)->comment('電話');
            $table->string('email', 45)->comment('email');
            $table->string('address', 45)->comment('地址');
            
            $table->integer('pay_type')->comment('0:paypal, 1:綠界');
            $table->integer('point')->nullable()->comment('點數');
            $table->integer('plan')->nullable()->comment('1:單次方案, 2:基礎方案, 3:高用量');
            
            $table->string('invoice_id', 45)->comment('發票編號');
            $table->text('memo')->nullable()->comment('備註');
            
            $table->timestamps();
            
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
