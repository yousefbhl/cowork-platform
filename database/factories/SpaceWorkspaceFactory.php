<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\WorkspaceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpaceWorkspaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'space_id'          => Space::factory(),
            'workspace_type_id' => WorkspaceType::inRandomOrder()->first()?->id ?? 1,
            'capacity'          => $this->faker->numberBetween(1, 20),
            'price_hourly'      => $this->faker->randomElement([null, 15, 20, 25, 30]),
            'price_daily'       => $this->faker->randomFloat(2, 20, 80),
            'price_monthly'     => $this->faker->randomFloat(2, 200, 800),
            'currency'          => 'USD',
            'is_active'         => true,
        ];
    }
}
