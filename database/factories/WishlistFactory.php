<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WishList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = WishList::class;
    public function definition(): array
    {
        return [
            'user_id' => User::get()->random()->id,
        ];
    }
}
