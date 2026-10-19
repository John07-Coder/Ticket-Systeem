<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_users_receive_roles_and_recognizable_names_without_duplicates(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        User::factory(19)->create();

        $originalUsers = User::orderBy('id')->get(['id', 'email', 'password'])->makeVisible('password')->toArray();

        $this->seed(UserRoleSeeder::class);
        $this->seed(UserRoleSeeder::class);

        $this->assertDatabaseCount('users', 20);
        $this->assertSame(12, User::where('role', 'employee')->count());
        $this->assertSame(7, User::where('role', 'agent')->count());
        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Admin',
            'role' => 'admin',
        ]);

        foreach (range(1, 7) as $number) {
            $this->assertDatabaseHas('users', ['name' => 'Agent '.$number, 'role' => 'agent']);
        }

        $this->assertSame($originalUsers, User::orderBy('id')->get(['id', 'email', 'password'])->makeVisible('password')->toArray());
    }

    public function test_newly_registered_users_default_to_employee(): void
    {
        $user = new User;
        $user->name = 'Nieuwe medewerker';
        $user->email = 'medewerker@example.com';
        $user->password = 'password';
        $user->save();

        $this->assertSame('employee', $user->fresh()->role);
    }
}
