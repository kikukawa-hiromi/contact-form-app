<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => fn () => Category::inRandomOrder()->value('id'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            // 1〜3のランダムな性別
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->safeEmail(),
            'tel' => fake()->phoneNumber(),
            'address' => fake()->prefecture() . fake()->streetAddress(),
            'building' => fake()->secondaryAddress(),
            'detail' => fake()->realText(120),
        ];
    }
}
