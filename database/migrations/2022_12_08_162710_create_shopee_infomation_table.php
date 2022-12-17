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
        Schema::create('shopee_information', function (Blueprint $table) {
            $table->id();               
            $table->integer('partner_id');
            $table->string('partner_key',255);
            $table->string('access_token',255);            
            $table->string('refresh_token',255);  
            $table->integer('expired_time',255);  
            $table->integer('shop_id');            
            $table->string('shop_name',255);
            $table->tinyInteger('status');
            $table->dateTime('deleted') ;
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
        Schema::dropIfExists('shopee_infomation');
    }
};
