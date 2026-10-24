<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Index mempercepat pencarian dan filter (status, tanggal, nama produk)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('status');
            $table->index('purchased_at');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->index('status');
            $table->index('sold_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['purchased_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['sold_at']);
        });
    }
};
