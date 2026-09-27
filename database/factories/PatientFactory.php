<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\InsuranceType;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $gender = $this->faker->randomElement(Gender::cases());

        return [
            'mr_number' => str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->name($gender === Gender::MALE ? 'male' : 'female'),
            'nik' => $this->faker->unique()->numerify('################'),
            'gender' => $gender,
            'birth_date' => $this->faker->dateTimeBetween('-70 years', '-1 year'),
            'birth_place' => $this->faker->city(),
            'blood_type' => $this->faker->randomElement(['A', 'B', 'AB', 'O']),
            'marital_status' => $this->faker->randomElement(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']),
            'phone' => $this->faker->numerify('08##########'),
            'address' => $this->faker->address(),
            'village' => 'Kel '.$this->faker->word(),
            'district' => 'Kec '.$this->faker->word(),
            'city' => $this->faker->city(),
            'province' => 'Jawa Barat',
            'postal_code' => $this->faker->numerify('#####'),
            'insurance_type' => InsuranceType::NONE,
            'is_active' => true,
        ];
    }

    public function bpjs(): static
    {
        return $this->state(fn () => [
            'insurance_type' => InsuranceType::BPJS,
            'insurance_number' => $this->faker->unique()->numerify('0000000000000'),
            'insurance_name' => 'BPJS Kesehatan',
        ]);
    }
}
