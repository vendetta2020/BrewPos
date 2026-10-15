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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained();
            $table->foreignId('order_item_id')->nullable()->constrained();
            $table->enum('type', ['in', 'out', 'adjust']);
            $table->decimal('qty', 10, 2);
            $table->decimal('cost_per_unit', 10, 2);
            $table->decimal('total_cost', 12, 2);
            $table->string('note')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
