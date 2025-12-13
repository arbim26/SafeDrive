<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\DriverDetail;
use App\Models\Trip;
use App\Models\FatigueLog;
use App\Models\Alert;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin SafeDrive',
            'email' => 'admin@safedrive.com',
            'role' => 'admin',
            'password' => bcrypt('admin123'),
        ]);

        // Create companies
        $companies = Company::factory()->count(3)->create();

        foreach ($companies as $company) {
            // Create company user
            $companyUser = User::factory()->create([
                'name' => $company->name . ' Admin',
                'email' => 'admin@' . strtolower(str_replace(' ', '', $company->name)) . '.com',
                'role' => 'company',
                'company_id' => $company->id,
                'password' => bcrypt('company123'),
            ]);

            // Create drivers for each company
            $drivers = User::factory()
                ->count(5)
                ->driver()
                ->create([
                    'company_id' => $company->id,
                ]);

            foreach ($drivers as $driver) {
                // Create driver details
                DriverDetail::factory()->create([
                    'user_id' => $driver->id,
                ]);

                // Create trips for driver
                $trips = Trip::factory()
                    ->count(fake()->numberBetween(2, 10))
                    ->create([
                        'driver_id' => $driver->id,
                    ]);

                foreach ($trips as $trip) {
                    // Create fatigue logs for each trip
                    $fatigueLogs = FatigueLog::factory()
                        ->count(fake()->numberBetween(5, 20))
                        ->create([
                            'trip_id' => $trip->id,
                            'driver_id' => $driver->id,
                        ]);

                    // Create alerts based on fatigue logs
                    foreach ($fatigueLogs as $log) {
                        if ($log->fatigue_score > 0.8) {
                            Alert::factory()->create([
                                'driver_id' => $driver->id,
                                'trip_id' => $trip->id,
                                'type' => 'fatigue_high',
                                'message' => 'High fatigue detected: ' . ($log->fatigue_score * 100) . '%',
                                'severity' => 'high',
                            ]);
                        }

                        if (!$log->seatbelt_on) {
                            Alert::factory()->create([
                                'driver_id' => $driver->id,
                                'trip_id' => $trip->id,
                                'type' => 'no_seatbelt',
                                'message' => 'Seatbelt not detected',
                                'severity' => 'medium',
                            ]);
                        }
                    }
                }
            }
        }

        // Create independent drivers (without company)
        $independentDrivers = User::factory()
            ->count(3)
            ->driver()
            ->create([
                'company_id' => null,
            ]);

        foreach ($independentDrivers as $driver) {
            DriverDetail::factory()->create([
                'user_id' => $driver->id,
            ]);
        }

        // Create family users
        User::factory()
            ->count(2)
            ->family()
            ->create();

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin credentials:');
        $this->command->info('Email: admin@safedrive.com');
        $this->command->info('Password: admin123');
    }
}