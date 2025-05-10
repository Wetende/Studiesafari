<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SubscriptionTier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionTier>
 */
final class SubscriptionTierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SubscriptionTier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Basic', 'Standard', 'Premium', 'Ultimate', 'Pro']) . ' Plan';
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price_monthly' => fake()->numberBetween(499, 2999), // in cents
            'price_yearly' => fake()->numberBetween(4990, 29990), // in cents
            'currency' => 'USD',
            'max_courses' => fake()->optional()->numberBetween(10, 100),
            'features' => [
                'feature_1' => 'Access to ' . fake()->numberBetween(10, 500) . '+ courses',
                'feature_2' => fake()->randomElement(['HD video quality', '4K video quality']),
                'feature_3' => fake()->randomElement(['Offline downloads', 'Certificate of completion']),
                'feature_4' => fake()->optional()->randomElement(['1-on-1 mentoring', 'Priority support']),
            ],
            'badge_text' => fake()->optional()->randomElement(['POPULAR', 'BEST VALUE', 'NEW']),
            'is_active' => true,
            'trial_days' => fake()->optional()->numberBetween(7, 30),
            'display_order' => fake()->numberBetween(1, 5),
        ];
    }

    /**
     * Create a basic tier.
     */
    public function basic(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Basic Plan',
            'slug' => 'basic-plan',
            'price_monthly' => 999, // $9.99
            'price_yearly' => 9990, // $99.90
            'max_courses' => 20,
            'features' => [
                'feature_1' => 'Access to 100+ courses',
                'feature_2' => 'HD video quality',
                'feature_3' => 'Certificate of completion',
            ],
            'display_order' => 1,
        ]);
    }

    /**
     * Create a premium tier.
     */
    public function premium(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Premium Plan',
            'slug' => 'premium-plan',
            'price_monthly' => 1999, // $19.99
            'price_yearly' => 19990, // $199.90
            'max_courses' => 50,
            'features' => [
                'feature_1' => 'Access to 300+ courses',
                'feature_2' => '4K video quality',
                'feature_3' => 'Certificate of completion',
                'feature_4' => 'Offline downloads',
                'feature_5' => 'Priority support',
            ],
            'badge_text' => 'POPULAR',
            'display_order' => 2,
        ]);
    }

    /**
     * Create an ultimate tier.
     */
    public function ultimate(): self
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Ultimate Plan',
            'slug' => 'ultimate-plan',
            'price_monthly' => 2999, // $29.99
            'price_yearly' => 29990, // $299.90
            'max_courses' => null, // unlimited
            'features' => [
                'feature_1' => 'Access to ALL courses',
                'feature_2' => '4K video quality',
                'feature_3' => 'Certificate of completion',
                'feature_4' => 'Offline downloads',
                'feature_5' => '1-on-1 mentoring sessions',
                'feature_6' => 'Priority support',
            ],
            'badge_text' => 'BEST VALUE',
            'display_order' => 3,
        ]);
    }

    /**
     * Indicate that the tier is inactive.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
} 