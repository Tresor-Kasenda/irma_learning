<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EnsureEventStatusEnum;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
final class BookingFactory extends Factory
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
            'company' => $this->faker->company,
            'sector' => $this->faker->word,
            'position' => $this->faker->word,
            'title' => $this->faker->word,
            'name' => $this->faker->name,
            'firstname' => $this->faker->firstName,
            'email' => $this->faker->email,
            'office_phone' => $this->faker->phoneNumber,
            'phone_number' => $this->faker->phoneNumber,
            'town' => $this->faker->city,
            'status' => $this->faker->randomElement(EnsureEventStatusEnum::cases()),
            'reference' => $this->faker->uuid,
        ];
    }
}
