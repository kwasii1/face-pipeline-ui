<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'batch_id' => null,
            'path' => fake()->filePath(),
            'status' => fake()->randomElement(['pending', 'processed', 'failed']),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
        ]);
    }
}
