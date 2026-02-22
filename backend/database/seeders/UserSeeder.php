<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Matt',
            'email' => 'matt@goteam.ph',
            'password' => bcrypt('Pa$$w0rd!'),
        ]);
    }
}
