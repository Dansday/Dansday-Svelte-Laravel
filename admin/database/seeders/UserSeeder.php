<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        if (User::count() > 0) {
            return;
        }
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'admin@domain.com',
            'password' => Hash::make('12345678'),
        ]);
    }
}
