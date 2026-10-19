<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasRole = Schema::hasColumn('users', 'role');

        Schema::table('users', function (Blueprint $table) use ($hasRole): void {
            $column = $table->string('role')->default('employee');

            if ($hasRole) {
                $column->change();
            }
        });

        DB::table('users')->where('role', 'werknemer')->update(['role' => 'employee']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('werknemer')->change();
        });

        DB::table('users')->where('role', 'employee')->update(['role' => 'werknemer']);
    }
};
