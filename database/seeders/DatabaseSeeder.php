<?php
namespace Database\Seeders;

use App\Models\{Position, School, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([SchoolSeeder::class, PositionSeeder::class]);
        $school = School::where('name', 'Atulayan Elementary School')->first(); // used by the sample head and teacher

        $mk = function ($name, $email, $role, $pos, $office, $rec = null, $schoolId = null) {
            $p = Position::where('title', $pos)->orderBy('sort')->first();
            return User::create([
                'name' => $name, 'email' => $email, 'password' => Hash::make('password'),
                'role' => $role, 'position' => $p?->title ?? $pos, 'position_id' => $p?->id,
                'office' => $office, 'recommender_id' => $rec, 'school_id' => $schoolId,
            ]);
        };

        $mk('System Admin', 'admin@sdotuguegarao.test', 'admin', 'Administrator', 'SDO Tuguegarao City');
        $mk('Sample SDS', 'sds@sdotuguegarao.test', 'sds', 'Schools Division Superintendent', 'SDO Tuguegarao City');
        $asds = $mk('Sample ASDS', 'asds@sdotuguegarao.test', 'asds', 'Assistant Schools Division Superintendent', 'SDO Tuguegarao City');
        $mk('Sample School Head', 'head@sdotuguegarao.test', 'school_head', 'School Principal I', $school->name, $asds->id, $school->id);
        $unitHead = $mk('Sample Unit Head', 'unithead@sdotuguegarao.test', 'unit_head', 'Chief Education Supervisor', 'CID', $asds->id);
        $mk('Sample Teacher', 'teacher@sdotuguegarao.test', 'teacher', 'Teacher I', $school->name, null, $school->id);
        $mk('Sample SDO Staff', 'staff@sdotuguegarao.test', 'unit_personnel', 'Administrative Assistant III', 'CID', $unitHead->id);
    }
}
