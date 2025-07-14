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
        Schema::create('feedbacks', function (Blueprint $table) {

            $table->string('id', 45)->primary()->comment('問題編號');
            
            $table->string('title')->comment('標題');
            $table->tinyInteger('status')->default(0)->comment('0:未處理(預設), 1:處理中, 2:已處理');
            
            $table->string('type', 45)->comment('問題類型');
            
            $table->string('member_id', 45)->comment('會員編號');
            
            $table->text('content')->nullable()->comment('內容');
            $table->text('other')->nullable()->comment('其他');
            
            $table->timestamps(); 
            
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
