<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name', 'products_name_search_index');
            $table->index('sold_count', 'products_sold_count_sort_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'orders_status_filter_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_name_search_index');
            $table->dropIndex('products_sold_count_sort_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_filter_index');
        });
    }
};
