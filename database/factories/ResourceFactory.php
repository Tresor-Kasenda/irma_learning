<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MasterClassResourceEnum;
use App\Models\MasterClass;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resource>
 */
final class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'master_class_id' => MasterClass::factory(),
            'title' => $this->faker->sentence,
            'type' => $this->faker->randomElement(MasterClassResourceEnum::cases()),
            'content' => $this->faker->paragraph,
            'file_path' => $this->faker->file('storage/app/public'),
        ];
    }
}
