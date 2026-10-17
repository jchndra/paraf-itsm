<?php

namespace Database\Factories;

use App\Models\Authority;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuthorityFactory extends Factory
{
    protected $model = Authority::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'nip' => $this->faker->unique()->numerify('19################'),
            'position' => 'Kepala Bidang IT',
            'authority_type' => 'cab_member',
            'is_active' => true,
        ];
    }
}
