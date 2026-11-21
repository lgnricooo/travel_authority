<?php
namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

/** Public basic education schools, SDO Tuguegarao City. Safe to run more than once. */
class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $list = [
            'Elementary School' => [
                'Atulayan Elementary School', 'Balzain East Elementary School', 'Buntun Elementary School',
                'Carig Norte Elementary School', 'Caritan Norte Elementary School', 'Cataggaman Elementary School',
                'Cataggaman Nuevo Elementary School', 'Dadda Elementary School', 'Gosi Elementary School',
                'Larion Alto Elementary School', 'Larion Bajo Elementary School', 'Linao Elementary School',
                'Pardo Elementary School', 'Pengue-Ruyu Elementary School', 'San Gabriel Elementary School',
                'Tagga Elementary School', 'Tuguegarao East Central School', 'Tuguegarao North Central School',
                'Tuguegarao West Central School',
            ],
            'Integrated School' => [
                'Annafunan Integrated School', 'Bagay Integrated School', 'Capatan Integrated School',
                'Carig Integrated School', 'Libag Integrated School', 'Namabbalan Integrated School',
                'Pallua Integrated School', 'Tuguegarao Northeast Central Integrated School', 'Ugac Integrated School',
            ],
            'High School' => [
                'Cagayan National High School – Junior High School', 'Cagayan National High School – Senior High School',
                'Cataggaman National High School', 'Gosi National High School', 'Linao National High School',
                'Tuguegarao City Science High School', 'Tuguegarao City West High School',
            ],
        ];
        foreach ($list as $level => $names) {
            foreach ($names as $name) School::updateOrCreate(['name' => $name], ['level' => $level]);
        }
    }
}
