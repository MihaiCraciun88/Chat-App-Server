<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');

        $admin = User::factory()->role(User::ROLE_ADMIN)->create();
        
        $users = User::factory()
            ->count(5)
            ->create();

        foreach ($users as $user) {
            $conversation = Conversation::factory()->create();
            Message::factory()
                ->user($user)
                ->conversation($conversation)
                ->count(5)
                ->create();
            DB::table('conversation_user')->insert([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('conversation_user')->insert([
                'conversation_id' => $conversation->id,
                'user_id' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
    }
}
