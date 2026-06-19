<?php

namespace Database\Factories;

use App\Models\PhotoBatch;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoBatchFactory extends Factory
{
    protected $model = PhotoBatch::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(100, 500);

        return [
            'project_id' => Project::factory(),
            'total_photos' => $total,
            'processed_photos' => fake()->numberBetween(0, $total),
            'status' => fake()->randomElement(['pending', 'processing', 'clustering', 'done']),
        ];
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'done',
            'processed_photos' => $attributes['total_photos'],
        ]);
    }
}
