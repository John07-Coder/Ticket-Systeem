<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $testUser = User::firstWhere('email', 'test@example.com')
                ?? User::factory()->create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ]);

            $users = User::factory(19)->create()->push($testUser);

            $categories = Category::factory()
                ->count(5)
                ->sequence(
                    ['name' => 'Hardware'],
                    ['name' => 'Software'],
                    ['name' => 'Netwerk'],
                    ['name' => 'Accounts'],
                    ['name' => 'Overig'],
                )
                ->create();

            Ticket::factory()
                ->count(50)
                ->recycle($users)
                ->recycle($categories)
                ->has(Comment::factory()->count(3)->recycle($users))
                ->create();
        });
    }
}
