<?php
namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    private const LEVELS = ['Elementary School', 'Integrated School', 'High School'];

    public function index()
    {
        return view('admin.schools', [
            'schools' => School::with('head')->withCount('users')->orderBy('name')->get(),
            'levels' => self::LEVELS,
        ]);
    }

    /** Add one school, or many at once (one name per line). */
    public function store(Request $r)
    {
        $d = $r->validate([
            'names' => 'required|string', 'district' => 'nullable|string|max:255',
            'level' => ['nullable', Rule::in(self::LEVELS)],
        ]);
        $names = collect(preg_split('/\r\n|\r|\n/', $d['names']))->map(fn($n) => trim($n))->filter()->unique();
        $added = 0;
        foreach ($names as $n) {
            if (strlen($n) > 255) continue;
            $school = School::firstOrCreate(['name' => $n], ['district' => $d['district'] ?? null, 'level' => $d['level'] ?? null]);
            $added += $school->wasRecentlyCreated ? 1 : 0;
        }
        $skipped = $names->count() - $added;
        return response()->json(['message' => "$added school(s) added" . ($skipped ? ", $skipped already existed." : '.')]);
    }

    public function update(Request $r, School $school)
    {
        $d = $r->validate([
            'name' => 'required|string|max:255|unique:schools,name,' . $school->id,
            'level' => ['nullable', Rule::in(self::LEVELS)],
            'district' => 'nullable|string|max:255',
            'school_code' => 'nullable|string|max:50',
        ]);
        $school->update($d);
        $school->users()->update(['office' => $school->name]); // keep printed "Permanent Station" in sync
        return response()->json(['message' => 'School updated.']);
    }

    public function destroy(School $school)
    {
        $n = $school->users()->count();
        if ($n > 0) {
            return response()->json(['message' => "{$school->name} still has $n assigned staff. Move them to another school first."], 422);
        }
        $school->delete();
        return response()->json(['message' => 'School deleted.']);
    }
}
