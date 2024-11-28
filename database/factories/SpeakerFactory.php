<?php

namespace Database\Factories;

use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Speaker;

class SpeakerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Speaker::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'avatar' => $this->faker->word(),
            'email' => $this->faker->safeEmail(),
            'qualifications' => [],
            'phone' => $this->faker->phoneNumber(),
            'bio' => $this->faker->text(),
            'twitter' => $this->faker->word(),
            'linkedin' => $this->faker->word(),
        ];
    }
    public function withTalks(int $count = 1): self
    {
        return $this->has(Talk::factory()->count($count), 'talks');
    }
}
