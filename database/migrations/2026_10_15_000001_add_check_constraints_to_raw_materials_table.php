<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Enforces non-negative stock_qty and min_stock_qty at the database engine level (MySQL 8.0+).
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE raw_materials ADD CONSTRAINT chk_raw_materials_stock_qty_non_negative CHECK (stock_qty >= 0)');
            DB::statement('ALTER TABLE raw_materials ADD CONSTRAINT chk_raw_materials_min_stock_qty_non_negative CHECK (min_stock_qty >= 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE raw_materials DROP CHECK chk_raw_materials_stock_qty_non_negative');
            DB::statement('ALTER TABLE raw_materials DROP CHECK chk_raw_materials_min_stock_qty_non_negative');
        }
    }
};
