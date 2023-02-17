<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filename = 'countries.sql';
        $sql = base_path('database/seeders/'.$filename);
        if(file_exists($sql))
        {
            $dump = file_get_contents($sql);
            \DB::unprepared($dump);
        }
    }
}
