<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->username = 'admin';
        $user->email = 'fisasti@gmail.com';
        $user->password = bcrypt('l1st3n1ng$$$');
        $user->api_token = Str::random(60);
        $user->save();
    }
}
