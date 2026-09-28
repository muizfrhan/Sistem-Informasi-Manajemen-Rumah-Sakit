<?php

namespace Database\Factories;

use App\Models\DemoRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DemoRequest> */
class DemoRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'institution' => fake()->company().' Rumah Sakit',
            'position' => fake()->randomElement(['Direktur', 'KepalaInstalasi', 'Manajer']),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08'.fake()->numerify('##########'),
            'facility_type' => fake()->randomElement(['HOSPITAL', 'CLINIC', 'PRIVATE_PRACTICE', 'OTHER']),
            'bed_count' => fake()->numberBetween(20, 500),
            'message' => fake()->sentence(10),
            'status' => 'NEW',
            'internal_note' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => 'Seeded by factory.',
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
