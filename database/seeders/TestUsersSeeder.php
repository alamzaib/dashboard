<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('test1234');
        
        for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name' => "Test User {$i}",
                'email' => "testuser{$i}@example.com",
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }
        
        $this->command->info('Created 20 test users successfully!');
        $this->command->info('All users have password: test1234');
    }
}

