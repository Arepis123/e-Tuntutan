<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@clab.com.my'],
            [
                'name'     => 'Admin CLAB',
                'password' => Hash::make('password'),
                'phone'    => '0312345678',
            ]
        );
        $admin->assignRole('admin');

        // PICs
        $pics = [
            ['name' => 'En Razali',   'email' => 'razali@clab.com.my'],
            ['name' => 'En Razi',     'email' => 'razi@clab.com.my'],
            ['name' => 'Puan Winda',  'email' => 'winda@clab.com.my'],
            ['name' => 'Puan Farah',  'email' => 'farah@clab.com.my'],
            ['name' => 'En Seffri',   'email' => 'seffri@clab.com.my'],
        ];

        foreach ($pics as $picData) {
            $pic = User::firstOrCreate(
                ['email' => $picData['email']],
                [
                    'name'     => $picData['name'],
                    'password' => Hash::make('password'),
                ]
            );
            $pic->assignRole('pic');
        }

        // Demo employer
        $employer = User::firstOrCreate(
            ['email' => 'employer@demo.com'],
            [
                'name'         => 'Demo Employer',
                'password'     => Hash::make('password'),
                'phone'        => '0123456789',
                'company_name' => 'Demo Construction Sdn Bhd',
            ]
        );
        $employer->assignRole('employer');
    }
}
