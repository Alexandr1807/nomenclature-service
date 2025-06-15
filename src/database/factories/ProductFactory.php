<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;                // ← импортируем
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $categoryId = Category::inRandomOrder()->value('id');
        $supplierId = Supplier::inRandomOrder()->value('id');
        $userId     = User::inRandomOrder()->value('id')
            ?? (string) Str::uuid();

        return [
            'id'           => (string) Str::uuid(),
            'name'         => $this->faker->words(3, true),
            'description'  => $this->faker->sentence(),
            'category_id'  => $categoryId,
            'supplier_id'  => $supplierId,
            'price'        => $this->faker->randomFloat(2, 1, 1000),
            'file_url'     => null,
            'is_active'    => true,
            'created_by'   => $userId,
            'updated_by'   => $userId,
        ];
    }
}
