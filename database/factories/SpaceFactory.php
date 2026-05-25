<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SpaceFactory extends Factory
{
    public function definition(): array
    {
        $title = $this->faker->company() . ' ' .
                 $this->faker->randomElement(['Hub', 'Space', 'Offices', 'Studio', 'Lounge']);

        $cities = ['Casablanca', 'Rabat', 'Paris', 'London', 'Dubai', 'New York'];
        $city   = $this->faker->randomElement($cities);

        return [
            'host_id'       => User::factory()->state(['role' => 'host']),
            'title'         => $title,
            'slug'          => Str::slug($title) . '-' . Str::random(5),
            'description'   => $this->faker->paragraphs(3, true),
            'address'       => $this->faker->address(),
            'lat'           => $this->faker->latitude(30, 51),
            'lng'           => $this->faker->longitude(-10, 55),
            'city'          => $city,
            'country'       => $this->faker->country(),
            'status'        => 'published',
            'rating_avg'    => $this->faker->randomFloat(2, 3.5, 5.0),
            'reviews_count' => $this->faker->numberBetween(0, 120),
        ];
    }
}
