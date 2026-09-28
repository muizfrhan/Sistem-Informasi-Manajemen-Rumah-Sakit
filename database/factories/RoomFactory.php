<?php

namespace Database\Factories;

use App\Enums\RoomClass;
use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Room> */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(100, 999);

        return [
            'code' => (string) $number,
            'name' => 'Kamar '.fake()->randomElement(['Melati', 'Anggrek', 'Kenanga', 'Melur', 'Mawar']).' '.$number,
            'room_class' => fake()->randomElement(RoomClass::cases()),
            'capacity' => 1,
            'status' => RoomStatus::AVAILABLE,
            'gender_policy' => 'ANY',
            'daily_rate' => fake()->numberBetween(200_000, 900_000),
            'notes' => null,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => RoomStatus::MAINTENANCE]);
    }
}
