<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\SpaceWorkspace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 week', '+2 months');
        $end   = $this->faker->dateTimeBetween($start, '+3 months');

        return [
            'customer_id'       => User::factory()->state(['role' => 'customer']),
            'space_workspace_id' => SpaceWorkspace::factory(),
            'start_date'        => $start->format('Y-m-d'),
            'end_date'          => $end->format('Y-m-d'),
            'status'            => $this->faker->randomElement(['pending', 'confirmed']),
            'total_amount'      => $this->faker->randomFloat(2, 50, 1000),
            'currency'          => 'USD',
        ];
    }
}
