<?php

namespace Database\Seeders;

use App\Models\Upload;
use App\Models\Record;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Zorg dat er een user is
        $user = User::first() ?? User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Maak een test upload aan
        $upload = Upload::create([
            'filename' => 'test-data.xlsx',
            'status' => 'completed',
            'processed_rows' => 3,
            'user_id' => $user->id,
        ]);

        // Voeg test records toe met jouw kolommen
        $testData = [
            [
                'date' => '2026-04-01',
                'action' => 'Onderhoud',
                'description' => 'Server update',
                'worker' => 'Jan Jansen',
                'time' => 2.5,
                'costs' => 250,
            ],
            [
                'date' => '2026-04-02',
                'action' => 'Bug fix',
                'description' => 'Database query optimization',
                'worker' => 'Marie Pieterse',
                'time' => 4,
                'costs' => 400,
            ],
            [
                'date' => '2026-04-03',
                'action' => 'Implementatie',
                'description' => 'New API endpoint',
                'worker' => 'Peter Hendriks',
                'time' => 6,
                'costs' => 600,
            ],
        ];

        foreach ($testData as $data) {
            Record::create([
                'upload_id' => $upload->bestand_id,
                'date' => $data['date'],
                'action' => $data['action'],
                'description' => $data['description'],
                'worker' => $data['worker'],
                'time' => $data['time'],
                'costs' => $data['costs'],
                'user_id' => $user->id,
            ]);
        }

        \Illuminate\Support\Facades\Log::info('Test data ingevoerd! 3 records aangemaakt.');
    }
}
