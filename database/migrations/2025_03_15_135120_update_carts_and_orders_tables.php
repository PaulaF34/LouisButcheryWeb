<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify 'carts' table to add 'order_id' only if it doesn't exist
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'order_id')) {
                $table->foreignId('order_id')
                      ->nullable()  // Make nullable if you want carts without orders
                      ->constrained('orders')  // Referencing the 'orders' table
                      ->onDelete('cascade')    // Delete cart items when order is deleted
                      ->after('user_id');      // Specify where to place this column
            }
        });

        // Modify 'orders' table to drop foreign key and column 'product_id'
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign key constraint on 'product_id' before dropping the column
            if ($this->hasForeignKey('orders', 'product_id')) {
                $table->dropForeign(['product_id']);  // Drop foreign key for 'product_id'
            }
            $table->dropColumn('product_id');   // Remove product_id
            $table->dropColumn('quantity');     // Remove quantity
            $table->dropColumn('price');        // Remove price
            $table->dropColumn('amount');       // Remove amount
        });
    }

    public function down(): void
    {
        // Reverse the changes in case of rollback
        Schema::table('carts', function (Blueprint $table) {
            // Drop the 'order_id' column and its foreign key constraint
            if (Schema::hasColumn('carts', 'order_id')) {
                $table->dropForeign(['order_id']);  // Drop the foreign key constraint on 'order_id'
                $table->dropColumn('order_id');  // Drop the 'order_id' column from carts
            }
        });

        // Reverse changes on the 'orders' table
        Schema::table('orders', function (Blueprint $table) {
            // Add product_id back to orders table
            $table->integer('product_id');
            $table->integer('quantity');
            $table->float('price');
            $table->float('amount');
        });
    }

    // Helper method to check if a foreign key exists
    private function hasForeignKey($table, $column)
    {
        $foreignKeys = DB::select("SHOW CREATE TABLE {$table}");
        $foreignKeyDefinition = $foreignKeys[0]->{"Create Table"};

        // Look for the foreign key name dynamically by checking if the table's foreign key exists
        return strpos($foreignKeyDefinition, "FOREIGN KEY (`{$column}`)") !== false;
    }
};
