<?php
namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

/** DepEd plantilla positions, SDO to school level. Safe to run more than once. */
class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // [title, note, salary grade]
        $groups = [
            'Executive Leadership (Office of the SDS)' => [
                ['Schools Division Superintendent', null, 'SG 26'],
                ['Assistant Schools Division Superintendent', null, 'SG 25'],
            ],
            'OSDS – Management & Legal' => [
                ['Administrative Officer V', 'Administrative Services / HRMO III / Budget Officer III', 'SG 18'],
                ['Attorney III', null, 'SG 21'],
                ['Legal Assistant I', null, 'SG 10'],
                ['Information Technology Officer I', null, 'SG 19'],
            ],
            'OSDS – Finance & Accounting' => [
                ['Accountant III', null, 'SG 19'],
                ['Accountant I', null, 'SG 12'],
                ['Administrative Officer IV', 'Budget / Cash / Supply / HRMO II / Records', 'SG 15'],
                ['Administrative Officer II', 'HR / Admin / Finance', 'SG 11'],
                ['Administrative Assistant III', 'Senior Bookkeeper / Secretary II', 'SG 9'],
                ['Administrative Assistant II', 'Disbursing Officer II / Clerk IV', 'SG 8'],
                ['Administrative Assistant I', 'Bookkeeper / Clerk III', 'SG 7'],
            ],
            'OSDS – Clerical, Utility & Security' => [
                ['Administrative Aide VI', 'Clerk III / Data Controller I / Utility Foreman', 'SG 6'],
                ['Administrative Aide IV', 'Clerk II / Driver II / Accounting Clerk I', 'SG 4'],
                ['Administrative Aide III', 'Clerk I / Driver I / Utility Worker II', 'SG 3'],
                ['Administrative Aide I', 'Utility Worker I', 'SG 1'],
                ['Security Guard I', null, 'SG 3'],
                ['Security Guard II', null, 'SG 5'],
                ['Watchman I', null, 'SG 2'],
            ],
            'Curriculum Implementation Division (CID)' => [
                ['Chief Education Supervisor', 'CID Chief', 'SG 24'],
                ['Public Schools District Supervisor', null, 'SG 22'],
                ['Education Program Supervisor', null, 'SG 22'],
                ['Education Program Specialist II', 'ALS / LRMS', 'SG 16'],
                ['Librarian II', null, 'SG 15'],
                ['Project Development Officer II', 'LRMS', 'SG 15'],
                ['Teaching Aids Specialist', null, 'SG 11'],
            ],
            'School Governance and Operations Division (SGOD)' => [
                ['Chief Education Supervisor', 'SGOD Chief', 'SG 24'],
                ['Senior Education Program Specialist', 'Planning & Research / M&E / HRD / SocMob', 'SG 19'],
                ['Planning Officer III', null, 'SG 18'],
                ['Education Program Specialist II', 'M&E / HRD / SocMob', 'SG 16'],
                ['Project Development Officer II', 'DRRM', 'SG 15'],
                ['Project Development Officer I', 'Youth Formation', 'SG 11'],
                ['Statistician I', null, 'SG 11'],
                ['Engineer III', null, 'SG 19'],
                ['Engineer II', null, 'SG 16'],
                ['Draftsman II', null, 'SG 8'],
                ['Medical Officer III', null, 'SG 21'],
                ['Dentist II', null, 'SG 17'],
                ['Dentist I', null, 'SG 14'],
                ['Nurse II', null, 'SG 16'],
                ['Nurse I', null, 'SG 15'],
                ['Dental Aide', null, 'SG 4'],
            ],
            'School Heads' => [
                ['School Principal IV', null, 'SG 22'],
                ['School Principal III', null, 'SG 21'],
                ['School Principal II', null, 'SG 20'],
                ['School Principal I', null, 'SG 19'],
                ['Special School Principal I', null, 'SG 19 – 20'],
                ['Special School Principal II', null, 'SG 19 – 20'],
                ['Vocational School Administrator I', null, 'SG 22 – 24'],
                ['Vocational School Administrator II', null, 'SG 22 – 24'],
                ['Vocational School Administrator III', null, 'SG 22 – 24'],
                ['Assistant School Principal II', 'phasing out under ECP', 'SG 19'],
                ['Head Teacher I', 'phasing out under ECP', 'SG 14 – 19'],
                ['Head Teacher II', 'phasing out under ECP', 'SG 14 – 19'],
                ['Head Teacher III', 'phasing out under ECP', 'SG 14 – 19'],
                ['Head Teacher IV', 'phasing out under ECP', 'SG 14 – 19'],
                ['Head Teacher V', 'phasing out under ECP', 'SG 14 – 19'],
                ['Head Teacher VI', 'phasing out under ECP', 'SG 14 – 19'],
            ],
            'Classroom Teaching' => [
                ['Master Teacher V', null, 'SG 22'], ['Master Teacher IV', null, 'SG 21'],
                ['Master Teacher III', null, 'SG 20'], ['Master Teacher II', null, 'SG 19'],
                ['Master Teacher I', null, 'SG 18'],
                ['Teacher VII', null, 'SG 17'], ['Teacher VI', null, 'SG 16'], ['Teacher V', null, 'SG 15'],
                ['Teacher IV', null, 'SG 14'], ['Teacher III', null, 'SG 13'], ['Teacher II', null, 'SG 12'],
                ['Teacher I', null, 'SG 11'],
                ['Special Science Teacher I', null, 'SG 13 – 24'], ['Special Science Teacher II', null, 'SG 13 – 24'],
                ['Special Science Teacher III', null, 'SG 13 – 24'], ['Special Science Teacher IV', null, 'SG 13 – 24'],
                ['Special Science Teacher V', null, 'SG 13 – 24'],
                ['Special Education (SPED) Teacher I', null, 'SG 14 – 18'], ['Special Education (SPED) Teacher II', null, 'SG 14 – 18'],
                ['Special Education (SPED) Teacher III', null, 'SG 14 – 18'], ['Special Education (SPED) Teacher IV', null, 'SG 14 – 18'],
                ['Special Education (SPED) Teacher V', null, 'SG 14 – 18'],
            ],
            'School-Based Guidance, Program & Support Staff' => [
                ['Guidance Coordinator I', null, 'SG 14 – 16'], ['Guidance Coordinator II', null, 'SG 14 – 16'], ['Guidance Coordinator III', null, 'SG 14 – 16'],
                ['Guidance Counselor I', null, 'SG 11 – 13'], ['Guidance Counselor II', null, 'SG 11 – 13'], ['Guidance Counselor III', null, 'SG 11 – 13'],
                ['School Counselor Associate I', null, 'SG 11'],
                ['Project Development Officer I', 'School-Based', 'SG 11'],
                ['Registrar I', null, 'SG 11'],
                ['Librarian I', null, 'SG 11'],
                ['Administrative Officer II', 'School', 'SG 11'],
                ['Administrative Assistant III', 'Senior Bookkeeper (School)', 'SG 9'],
                ['Administrative Assistant II', 'Disbursing Officer II (School)', 'SG 8'],
                ['Administrative Aide VI', 'Clerk III / Lab Technician (School)', 'SG 6'],
                ['Administrative Aide IV', 'Clerk II (School)', 'SG 4'],
                ['Administrative Aide I', 'Utility Worker / Watchman (School)', 'SG 1 – 3'],
                ['Administrative Aide II', 'Utility Worker / Watchman (School)', 'SG 1 – 3'],
                ['Administrative Aide III', 'Utility Worker / Watchman (School)', 'SG 1 – 3'],
                ['Security Guard I', 'School', 'SG 3'],
            ],
        ];

        $sort = 0;
        foreach ($groups as $group => $items) {
            foreach ($items as [$title, $note, $sg]) {
                Position::updateOrCreate(
                    ['title' => $title, 'note' => $note, 'group' => $group],
                    ['salary_grade' => $sg, 'sort' => ++$sort]
                );
            }
        }
    }
}
