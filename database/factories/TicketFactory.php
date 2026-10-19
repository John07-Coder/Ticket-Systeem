<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->randomElement([
                'Printer werkt niet',
                'Geen verbinding met wifi',
                'Wachtwoord resetten',
                'Laptop start niet op',
                'Geen toegang tot gedeelde map',
                'E-mail wordt niet verzonden',
                'Software installeren',
                'Tweede scherm wordt niet herkend',
                'VPN-verbinding valt weg',
                'Computer is traag',
            ]),
            'description' => fake()->paragraphs(2, true),
            'status' => fake()->randomElement(['open', 'in_progress', 'closed']),
            'priority' => fake()->randomElement(['low', 'normal', 'high']),
        ];
    }
}
