<?php

namespace Database\Seeders;

use App\Models\CarType;
use App\Models\Extension;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Car Types
        $carTypes = [
            ['name' => 'NON EV', 'code' => 'non_ev', 'description' => 'Non-Electric Vehicle', 'sort_order' => 1],
            ['name' => 'EV', 'code' => 'ev', 'description' => 'Electric Vehicle', 'sort_order' => 2],
        ];

        foreach ($carTypes as $ct) {
            CarType::updateOrCreate(['code' => $ct['code']], $ct);
        }

        // Extensions
        $extensions = [
            ['name' => 'TLO', 'code' => 'tlo', 'description' => 'Total Loss Only', 'is_main_coverage' => true, 'max_vehicle_age' => null, 'sort_order' => 1],
            ['name' => 'COMPREHENSIVE', 'code' => 'comprehensive', 'description' => 'All Risk Coverage', 'is_main_coverage' => true, 'max_vehicle_age' => null, 'sort_order' => 2],
            ['name' => 'EQVET', 'code' => 'eqvet', 'description' => 'Earthquake, Volcanic Eruption, Tsunami', 'is_main_coverage' => false, 'max_vehicle_age' => null, 'sort_order' => 3],
            ['name' => 'TSHFL', 'code' => 'tshfl', 'description' => 'Typhoon, Storm, Hail, Flood, Landslide', 'is_main_coverage' => false, 'max_vehicle_age' => null, 'sort_order' => 4],
            ['name' => 'RSCC', 'code' => 'rscc', 'description' => 'Riot, Strike, Civil Commotion', 'is_main_coverage' => false, 'max_vehicle_age' => null, 'sort_order' => 5],
            ['name' => 'AUTHORIZED WORKSHOP', 'code' => 'authorized_workshop', 'description' => 'Repair at authorized workshop only', 'is_main_coverage' => false, 'max_vehicle_age' => 5, 'sort_order' => 6],
            ['name' => 'PERSONAL ACCIDENT DRIVER', 'code' => 'pa_driver', 'description' => 'Personal Accident for Driver', 'is_main_coverage' => false, 'max_vehicle_age' => null, 'sort_order' => 7],
            ['name' => 'PERSONAL ACCIDENT PENUMPANG', 'code' => 'pa_penumpang', 'description' => 'Personal Accident for Passengers', 'is_main_coverage' => false, 'max_vehicle_age' => null, 'sort_order' => 8],
        ];

        foreach ($extensions as $ext) {
            Extension::updateOrCreate(['code' => $ext['code']], $ext);
        }
    }
}
