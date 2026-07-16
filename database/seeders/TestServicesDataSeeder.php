<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class TestServicesDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentors = [
            ['Roger', 'Federer', 'roger.federer@example.com'],
            ['Rafael', 'Nadal', 'rafael.nadal@example.com'],
            ['Novak', 'Djokovic', 'novak.djokovic@example.com'],
            ['Serena', 'Williams', 'serena.williams@example.com'],
            ['Steffi', 'Graf', 'steffi.graf@example.com'],
            ['Pete', 'Sampras', 'pete.sampras@example.com'],
            ['Andre', 'Agassi', 'andre.agassi@example.com'],
            ['Bjorn', 'Borg', 'bjorn.borg@example.com'],
            ['Rod', 'Laver', 'rod.laver@example.com'],
            ['Jimmy', 'Connors', 'jimmy.connors@example.com'],
            ['John', 'McEnroe', 'john.mcenroe@example.com'],
            ['Ivan', 'Lendl', 'ivan.lendl@example.com'],
            ['Stefan', 'Edberg', 'stefan.edberg@example.com'],
            ['Boris', 'Becker', 'boris.becker@example.com'],
            ['Arthur', 'Ashe', 'arthur.ashe@example.com'],
        ];

        $coaches = [
            ['Carlos', 'Alcaraz', 'carlos.alcaraz@example.com'],
            ['Jannik', 'Sinner', 'jannik.sinner@example.com'],
            ['Daniil', 'Medvedev', 'daniil.medvedev@example.com'],
            ['Alexander', 'Zverev', 'alexander.zverev@example.com'],
            ['Holger', 'Rune', 'holger.rune@example.com'],
            ['Stefanos', 'Tsitsipas', 'stefanos.tsitsipas@example.com'],
            ['Casper', 'Ruud', 'casper.ruud@example.com'],
            ['Taylor', 'Fritz', 'taylor.fritz@example.com'],
            ['Andrey', 'Rublev', 'andrey.rublev@example.com'],
            ['Grigor', 'Dimitrov', 'grigor.dimitrov@example.com'],
            ['Alex', 'de Minaur', 'alex.deminaur@example.com'],
            ['Hubert', 'Hurkacz', 'hubert.hurkacz@example.com'],
            ['Tommy', 'Paul', 'tommy.paul@example.com'],
            ['Frances', 'Tiafoe', 'frances.tiafoe@example.com'],
            ['Ben', 'Shelton', 'ben.shelton@example.com'],
        ];

        $cities = ['Mohali', 'Chandigarh', 'Panchkula', 'Ludhiana', 'Jalandhar'];
        $states = ['Punjab', 'Haryana', 'Punjab', 'Punjab', 'Punjab'];

        // Seed Mentors
        foreach ($mentors as $index => $m) {
            $firstName = $m[0];
            $lastName = $m[1];
            $email = $m[2];
            
            $cityIndex = $index % count($cities);

            $user = User::create([
                'name' => "{$firstName} {$lastName}",
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'username' => User::generateUniqueUsername($email),
                'phone' => '9876543' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'role' => UserRole::Mentor,
                'status' => 'active',
                'city' => $cities[$cityIndex],
                'state' => $states[$cityIndex],
                'profile_title_ad' => "Mastering Tennis Fundamentals & Strategy with {$firstName}",
                'profile_lessons' => "I offer comprehensive tennis strategy sessions, focusing on singles and doubles match play positioning, mental training, and pre-match routines. Perfect for all skill levels.",
                'profile_bio' => "Hello, I am {$firstName} {$lastName}. I have been playing competitive tennis for over 15 years. My goal as a mentor is to help upcoming players develop a strong mental state and solid baseline game.",
                'profile_locations' => ['outdoor', 'online', 'student_choice'],
                'profile_rate' => 30.00 + ($index * 5),
                'profile_rate_details' => "Rates are negotiable based on the number of sessions booked in advance. Discount available for current PTL members.",
            ]);

            $user->assignRole('Mentor');
        }

        // Seed Coaches
        foreach ($coaches as $index => $c) {
            $firstName = $c[0];
            $lastName = $c[1];
            $email = $c[2];
            
            $cityIndex = $index % count($cities);

            $user = User::create([
                'name' => "{$firstName} {$lastName}",
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'username' => User::generateUniqueUsername($email),
                'phone' => '9886543' . str_pad($index, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password123'),
                'role' => UserRole::Coach,
                'status' => 'active',
                'city' => $cities[$cityIndex],
                'state' => $states[$cityIndex],
                'profile_title_ad' => "Pro Coaching & Technical Training with Coach {$firstName}",
                'profile_lessons' => "Learn advanced forehands, backhands, serves, and footwork. I utilize video analysis during lessons to correct form instantly and maximize player potential.",
                'profile_bio' => "Hi, I am Coach {$firstName} {$lastName}. I hold elite coaching certifications and have trained junior and amateur tennis champions. Let's work together to take your game to the next tier.",
                'profile_locations' => ['indoor', 'outdoor', 'travel'],
                'profile_rate' => 50.00 + ($index * 8),
                'profile_rate_details' => "Court rental fees are not included in the hourly rate. Group coaching rates available upon inquiry.",
            ]);

            $user->assignRole('Coach');
        }
    }
}
