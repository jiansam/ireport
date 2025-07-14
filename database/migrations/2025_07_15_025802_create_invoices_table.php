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
        Schema::create('invoices', function (Blueprint $table) {

            $table->string('id', 45)->primary()->comment('發票編號');
            
            $table->string('order_id', 45)->comment('訂單編號');
            
            $table->string('number', 20)->comment('發票號碼');
            $table->integer('type')->comment('2:二聯式發票, 3:三聯式發票');
            
            $table->string('seller_name', 50)->nullable()->comment('賣方名稱');
            $table->string('seller_uniform_number', 20)->nullable()->comment('賣方統一編號');
            
            $table->string('buyer_name', 20)->nullable()->comment('買方名稱');
            $table->string('buyer_uniform_number', 20)->nullable()->comment('買方統一編號');
            
            $table->integer('total_amount')->comment('含稅總額');
            $table->integer('tax_amount')->comment('稅額');
            
            $table->string('random_number', 20)->comment('隨機碼');
            
            $table->string('carrier_type', 20)->nullable()->comment('載具類型（如手機條碼、會員載具）');
            $table->string('carrier_number')->nullable()->comment('載具號碼');
            
            $table->timestamp('time')->comment('開立時間');
            $table->timestamps(); 

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
