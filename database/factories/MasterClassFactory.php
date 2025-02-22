<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MasterClassEnum;
use App\Models\Event;
use App\Models\MasterClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterClass>
 */
final class MasterClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'duration' => $this->faker->randomNumber(2),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'status' => $this->faker->randomElement(MasterClassEnum::cases()),
            'sub_title' => $this->faker->sentence,
            'presentation' => $this->faker->paragraph,
            'path' => $this->faker->url,
            'ended_at' => $this->faker->dateTime,
        ];
    }
}
