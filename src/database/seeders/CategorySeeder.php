<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;

class CategorySeeder extends Seeder
{
    /**
     * Run the category seeds.
     */
    public function run(): void
    {

        Log::info('AdminId is: ' . User::first()->id);
        $adminId = User::first()->id;

        $faker = Faker::create();
        $total = rand(30, 50);

        for ($i = 0; $i < 5; $i++) {
            Category::create([
                'name'        => ucfirst($faker->unique()->word),
                'parent_id'   => null,
                'created_by'  => $adminId,
                'updated_by'  => $adminId,
            ]);
        }

        while (Category::count() < $total) {
            $parent = Category::inRandomOrder()->first();
            Category::create([
                'name'        => ucfirst($faker->unique()->word),
                'parent_id'   => $parent->id,
                'created_by'  => $adminId,
                'updated_by'  => $adminId,
            ]);
        }
    }
}
