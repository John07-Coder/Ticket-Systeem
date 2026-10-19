<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            User::where('role', 'werknemer')->update(['role' => 'employee']);

            $admin = User::where('role', 'admin')->orderBy('id')->first()
                ?? User::firstWhere('email', 'test@example.com')
                ?? User::orderBy('id')->first()
                ?? User::factory()->create();

            $admin->forceFill(['name' => 'Admin', 'role' => 'admin'])->save();

            $agents = User::where('role', 'agent')->orderBy('id')->limit(7)->get();

            if ($agents->count() < 7) {
                $agents = $agents->merge(
                    User::where('role', 'employee')->orderBy('id')->limit(7 - $agents->count())->get(),
                );
            }

            if ($agents->count() < 7) {
                $agents = $agents->merge(User::factory(7 - $agents->count())->create());
            }

            foreach ($agents->values() as $index => $agent) {
                $agent->forceFill([
                    'name' => 'Agent '.($index + 1),
                    'role' => 'agent',
                ])->save();
            }
        });
    }
}
