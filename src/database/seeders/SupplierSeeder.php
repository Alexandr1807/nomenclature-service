<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\User;
use Faker\Factory as Faker;

class SupplierSeeder extends Seeder
{
    /**
     * Run the supplier seeds.
     */
    public function run(): void
    {
        $adminId = User::first()->id;
        $faker = Faker::create();

        for ($i = 0; $i < 100; $i++) {
            Supplier::create([
                'name'          => $faker->company,
                'phone'         => $faker->phoneNumber,
                'contact_name'  => $faker->name,
                'website'       => $faker->url,
                'description'   => $faker->sentence,
                'created_by'    => $adminId,
                'updated_by'    => $adminId,
            ]);
        }
    }
}
