<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EnsureEventStatusEnum;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
final class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_type_id' => EventType::factory(),
            'title' => $this->faker->sentence,
            'town' => $this->faker->city,
            'date' => $this->faker->date,
            'heure_debut' => $this->faker->time,
            'heure_fin' => $this->faker->time,
            'duration' => $this->faker->randomDigit(),
            'image' => $this->faker->imageUrl,
            'tarif_membre' => $this->faker->randomDigit(),
            'tarif_non_membre' => $this->faker->randomDigit(),
            'description' => $this->faker->text,
            'status' => $this->faker->randomElement(EnsureEventStatusEnum::cases()),
            'content' => $this->faker->text,
        ];
    }
}
