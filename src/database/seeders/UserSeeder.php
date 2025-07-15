<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = Hash::make('password');

        $users = [
                //管理者
            [
                'name' => '佐藤 太郎',
                'email' => 'admin@example.com',
                'password' => $password,
                'role' => 'admin',
                'email_verified_at' => now(),
            ],
                //従業員
            [
                'name' => '鈴木 花子',
                'email' => 'suzuki@example.com',
                'password' => $password,
                'role' => 'employee',
                'email_verified_at' => now(),
            ],
            [
                'name' => '佐々木 薫',
                'email' => 'sasaki@example.com',
                'password' => $password,
                'role' => 'employee',
                'email_verified_at' => now(),
            ],
            [
                'name' => '高橋 健一',
                'email' => 'takahashi@example.com',
                'password' => $password,
                'role' => 'employee',
                'email_verified_at' => now(),
],

            ];

            foreach ($users as $user) {
            User::create($user);
        }
        $this->command->info('Users seeded successfully.');
    }
}
