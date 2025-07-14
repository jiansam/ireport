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
        Schema::create('reports', function (Blueprint $table) {

            $table->string('id', 45)->primary()->comment('報告編號');
            
            $table->string('member_id', 45)->comment('會員編號');
            
            $table->text('report')->comment('輸入內容');
            $table->text('key_findings')->nullable()->comment('關鍵發現');
            $table->text('clinical_history')->nullable()->comment('臨床病史');
            $table->text('previous_report')->nullable()->comment('先前報告');
            $table->text('template')->nullable()->comment('報告模板');
            $table->text('content')->nullable()->comment('報告內容');
            
            $table->timestamps();
            
            $table->index('member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
