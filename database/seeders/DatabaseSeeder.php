<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Creates test users for each role: Admin, Cashier, and Barista.
     * Satisfies SRS: AUTH-03 — three roles with different access levels.
     * Satisfies SRS: AUTH-01, AUTH-02 — login credentials for testing.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@brewpos.test'],
            [
                'name' => 'Admin BrewPos',
                'password' => 'password',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Cashier User
        User::firstOrCreate(
            ['email' => 'cashier@brewpos.test'],
            [
                'name' => 'Cashier Budi',
                'password' => 'password',
                'role' => 'cashier',
                'is_active' => true,
            ]
        );

        // Barista User
        User::firstOrCreate(
            ['email' => 'barista@brewpos.test'],
            [
                'name' => 'Barista Siti',
                'password' => 'password',
                'role' => 'barista',
                'is_active' => true,
            ]
        );

        // Seed Sample Products, Categories, Raw Materials & Recipes
        $this->call(RecipeTestSeeder::class);
    }
}
