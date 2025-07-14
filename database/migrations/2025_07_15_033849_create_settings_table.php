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
        Schema::create('settings', function (Blueprint $table) {

            $table->string('id', 45)->primary()->comment('唯一識別碼');

            $table->string('member_id', 45)->unique()->comment('會員編號');

            $table->string('key', 100)->unique()->comment('設定項目名稱（唯一）');
            $table->text('value')->comment('設定內容(可存JSON格式)');
            $table->string('type', 50)->default('string')->comment('設定類型(string, number, boolean, json等)');
            
            $table->text('description')->nullable()->comment('設定說明');
            
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('最後更新時間');
            $table->string('updated_by', 45)->comment('更新者(用戶ID)');
            
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
