<?php

namespace Database\Factories;

use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaceFactory extends Factory
{
    protected $model = Face::class;

    public function definition(): array
    {
        return [
            'photo_id' => Photo::factory(),
            'person_id' => null,
            'cluster_id' => null,
            'bbox' => null,
            'crop_path' => fake()->filePath(),
            'det_score' => fake()->randomFloat(4, 0.5, 0.99),
        ];
    }

    public function tagged(?Person $person = null): static
    {
        return $this->state(fn (array $attributes) => [
            'person_id' => $person?->id ?? Person::factory(),
        ]);
    }

    public function untagged(): static
    {
        return $this->state(fn (array $attributes) => [
            'person_id' => null,
        ]);
    }
}
