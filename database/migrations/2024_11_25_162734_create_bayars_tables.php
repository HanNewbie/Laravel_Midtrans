<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bayars', function (Blueprint $table) {
            $table->string('orders_id')->primary(); // Kolom order_id sebagai primary key
            $table->string('mobil');
            $table->integer('harga');
            $table->string('nama');
            $table->string('nomor');            
            $table->integer('hari');
            $table->bigInteger('harga_total');
            $table->enum('status', ['Unpaid', 'Paid']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bayars');
    }
};
