<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class RecipeTestSeeder extends Seeder
{
    /**
     * Run the database seeders for testing recipe management.
     */
    public function run(): void
    {
        // Create categories
        $espressoBased = Category::firstOrCreate(['name' => 'Espresso Based']);
        $nonCoffee = Category::firstOrCreate(['name' => 'Non-Coffee']);
        $signature = Category::firstOrCreate(['name' => 'Signature']);
        $bakery = Category::firstOrCreate(['name' => 'Bakery']);

        // Create raw materials (if not exists)
        $materials = [
            ['name' => 'Espresso Beans', 'category' => 'Kopi', 'unit' => 'gram', 'cost_per_unit' => 500, 'stock_qty' => 5000, 'min_stock_qty' => 500],
            ['name' => 'Fresh Milk', 'category' => 'Susu', 'unit' => 'ml', 'cost_per_unit' => 50, 'stock_qty' => 20000, 'min_stock_qty' => 2000],
            ['name' => 'Green Tea Powder', 'category' => 'Teh', 'unit' => 'gram', 'cost_per_unit' => 800, 'stock_qty' => 1000, 'min_stock_qty' => 100],
            ['name' => 'Brown Sugar', 'category' => 'Sirup', 'unit' => 'gram', 'cost_per_unit' => 30, 'stock_qty' => 5000, 'min_stock_qty' => 500],
            ['name' => 'Tapioca Pearls', 'category' => 'Topping', 'unit' => 'gram', 'cost_per_unit' => 100, 'stock_qty' => 2000, 'min_stock_qty' => 200],
            ['name' => 'Cocoa Powder', 'category' => 'Bubuk', 'unit' => 'gram', 'cost_per_unit' => 150, 'stock_qty' => 500, 'min_stock_qty' => 50],
            ['name' => 'Butter', 'category' => 'Topping', 'unit' => 'gram', 'cost_per_unit' => 200, 'stock_qty' => 1000, 'min_stock_qty' => 100],
        ];

        foreach ($materials as $material) {
            RawMaterial::firstOrCreate(['name' => $material['name']], $material);
        }

        // Create products with recipes
        $products = [
            [
                'name' => 'Caffe Latte Hot',
                'description' => 'Minuman susu dengan satu shot espresso premium.',
                'category_id' => $espressoBased->id,
                'price' => 35000,
                'image' => null,
                'has_variants' => false,
                'recipes' => [
                    ['material' => 'Espresso Beans', 'qty' => 20],
                    ['material' => 'Fresh Milk', 'qty' => 200],
                ]
            ],
            [
                'name' => 'Iced Matcha Latte',
                'description' => 'Teh hijau jepang dengan susu segar dan es batu.',
                'category_id' => $nonCoffee->id,
                'price' => 40000,
                'image' => null,
                'has_variants' => false,
                'recipes' => [] // No recipe yet
            ],
            [
                'name' => 'Aren Brown Sugar',
                'description' => 'Kopi susu dengan gula aren organik cair.',
                'category_id' => $signature->id,
                'price' => 38000,
                'image' => null,
                'has_variants' => false,
                'recipes' => [
                    ['material' => 'Espresso Beans', 'qty' => 18],
                    ['material' => 'Fresh Milk', 'qty' => 180],
                    ['material' => 'Brown Sugar', 'qty' => 30],
                ]
            ],
            [
                'name' => 'Cappuccino',
                'description' => 'Kopi dengan busa susu tebal dan cokelat bubuk.',
                'category_id' => $espressoBased->id,
                'price' => 37000,
                'image' => null,
                'has_variants' => false,
                'recipes' => [
                    ['material' => 'Espresso Beans', 'qty' => 20],
                    ['material' => 'Fresh Milk', 'qty' => 200],
                    ['material' => 'Cocoa Powder', 'qty' => 5],
                ]
            ],
            [
                'name' => 'Iced Americano',
                'description' => 'Double shot espresso dengan air dan es batu.',
                'category_id' => $espressoBased->id,
                'price' => 32000,
                'image' => null,
                'has_variants' => false,
                'recipes' => [] // No recipe yet
            ],
            [
                'name' => 'Butter Croissant',
                'description' => 'Pastry klasik Perancis dengan mentega premium.',
                'category_id' => $bakery->id,
                'price' => 45000,
                'image' => null,
                'has_variants' => false,
                'recipes' => [
                    ['material' => 'Butter', 'qty' => 80],
                ]
            ],
        ];

        foreach ($products as $productData) {
            $recipes = $productData['recipes'];
            unset($productData['recipes']);
            
            $product = Product::firstOrCreate(['name' => $productData['name']], $productData);
            
            // Create recipes
            foreach ($recipes as $recipe) {
                $material = RawMaterial::where('name', $recipe['material'])->first();
                if ($material) {
                    ProductRecipe::firstOrCreate(
                        ['product_id' => $product->id, 'raw_material_id' => $material->id, 'size_variant' => 'all'],
                        ['qty_used' => $recipe['qty']]
                    );
                }
            }

            // Update has_recipe flag
            $product->update(['has_recipe' => $product->recipes()->exists()]);
        }

        $this->command->info('✅ Test data untuk recipe management berhasil dibuat!');
    }
}
