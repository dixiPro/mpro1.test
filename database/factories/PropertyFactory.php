<?php

// database/factories/PropertyFactory.php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    private array $houseNames = [
        'Victoria',
        'Xavier',
        'Como',
        'Aspen',
        'Lucretia',
        'Toorak',
        'Skyscape',
        'Clifton',
        'Geneva',
        // addition
        'Windsor',
        'Melbourne',
        'Brighton'

    ];

    public function definition(): array
    {
        return [
            'name' => 'The ' . $this->faker->randomElement($this->houseNames) . ' ' . $this->faker->unique()->numberBetween(1, 100000),
            'price' => $this->faker->numberBetween(250000, 600000),
            'bedrooms' => $this->faker->numberBetween(3, 5),
            'bathrooms' => $this->faker->numberBetween(2, 3),
            'storeys' => $this->faker->numberBetween(1, 2),
            'garages' => $this->faker->numberBetween(1, 3),
        ];
    }
}
