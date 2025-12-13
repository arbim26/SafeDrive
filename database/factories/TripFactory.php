<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-1 month', 'now');
        $endTime = fake()->dateTimeBetween($startTime, '+8 hours');
        
        return [
            'driver_id' => null, // Akan diisi di seeder
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_location' => fake()->city(),
            'end_location' => fake()->city(),
            'distance_km' => fake()->randomFloat(2, 5, 500),
            'status' => fake()->randomElement(['completed', 'active', 'cancelled']),
            'route_coordinates' => $this->generateRouteCoordinates(),
        ];
    }

    /**
     * Generate fake route coordinates.
     */
    private function generateRouteCoordinates(): array
    {
        $coordinates = [];
        $points = fake()->numberBetween(5, 20);
        
        for ($i = 0; $i < $points; $i++) {
            $coordinates[] = [
                'lat' => fake()->latitude(-6, -7),
                'lng' => fake()->longitude(106, 107),
                'timestamp' => fake()->dateTimeBetween('-1 hour', 'now')->format('Y-m-d H:i:s'),
                'fatigue_score' => fake()->randomFloat(2, 0, 1)
            ];
        }
        
        return $coordinates;
    }
}