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
        Schema::create('members', function (Blueprint $table) {

            $table->string('id', 45)->primary()->comment('會員編號');
            
            $table->string('name', 50)->comment('姓名');
            $table->string('phone', 10)->comment('電話(手機)');
            $table->string('email', 255)->unique()->comment('電子信箱');
            $table->string('address', 255)->comment('地址');
            
            $table->string('carrier_num', 255)->nullable()->comment('電子發票載具');
            $table->string('tax_id', 8)->nullable()->comment('統一編號');
            
            $table->string('account')->unique()->comment('帳號');
            $table->string('password')->comment('密碼');
            
            $table->string('google_id')->nullable()->comment('google唯一識別碼');

            $table->tinyInteger('status')->nullable()->default(0)->comment('0:試用期, 1:年訂閱, 2:月訂閱, 3:未訂閱, 4:未使用, 5:已購買, 6:一般免費');
            $table->integer('point')->nullable()->default(0)->comment('點數');
            
            $table->timestamp('login_time')->nullable()->comment('登入時間');
            $table->timestamp('start_time')->nullable()->comment('方案開始日期');
            $table->timestamp('end_time')->nullable()->comment('方案結束日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
