<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
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
            'first_name' => fake()->lastName(),
            'last_name' => fake()->firstName(),
            // 1〜3のランダムな性別
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->safeEmail(),
            'tel' => str_replace('-', '', fake()->phoneNumber()),
            'address' => fake()->prefecture().fake()->streetAddress(),
            'building' => fake()->secondaryAddress(),
            'detail' => fake()->realText(120),
        ];
    }
}
