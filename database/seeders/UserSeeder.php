<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users  = [];
        $pass = Hash::make("12345678");
        for ($i = 1; $i < 100; $i++) {
            $users[] = [
                "name"=> "User ".$i,
                "email"=> "user{$i}@gmail.com",
                "password"=> $pass,
            ];
        }
        DB::table('users')->insert($users);
    }
}
