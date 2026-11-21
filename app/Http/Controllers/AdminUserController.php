<?php
namespace App\Http\Controllers;

use App\Models\{Position, School, SignatureLog, TravelRequest, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users', [
            'users' => User::with('recommender', 'school')->orderBy('name')->get(),
            'roles' => User::ROLES,
            'schools' => School::orderBy('name')->get(),
            'positions' => Position::orderBy('sort')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|string|max:255', 'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8', 'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'position_id' => 'nullable|exists:positions,id', 'office' => 'nullable|string|max:255',
            'school_id' => 'nullable|exists:schools,id', 'recommender_id' => 'nullable|exists:users,id',
        ]);
        if ($err = $this->applySchool($d)) return response()->json(['message' => $err], 422);
        if (!empty($d['position_id'])) $d['position'] = Position::find($d['position_id'])->title;
        $d['password'] = Hash::make($d['password']);
        $user = User::create($d);
        $this->repointPending($user);
        return response()->json(['message' => 'User created.']);
    }

    public function update(Request $r, User $user)
    {
        $d = $r->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'position_id' => 'nullable|exists:positions,id',
            'office' => 'nullable|string|max:255',
            'school_id' => 'nullable|exists:schools,id',
            'recommender_id' => 'nullable|exists:users,id',
            'password' => 'nullable|string|min:8',
        ]);
        if ($user->id === auth()->id() && $d['role'] !== 'admin') {
            return response()->json(['message' => 'You cannot remove your own administrator role.'], 422);
        }
        if ($err = $this->applySchool($d, $user->id)) return response()->json(['message' => $err], 422);

        if (!empty($d['position_id'])) $d['position'] = Position::find($d['position_id'])->title; else unset($d['position_id']);
        if (blank($d['password'] ?? null)) unset($d['password']); else $d['password'] = Hash::make($d['password']);

        $user->update($d);
        $this->repointPending($user->fresh());
        return response()->json(['message' => 'User updated.']);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }
        // Travel authority records and their e-signature logs must be kept for audit
        $hasRecords = TravelRequest::where('user_id', $user->id)->orWhere('recommender_id', $user->id)->orWhere('approver_id', $user->id)->exists()
            || SignatureLog::where('user_id', $user->id)->exists();
        if ($hasRecords) {
            return response()->json(['message' => "{$user->name} has travel authority records, which must be kept for audit, so the account cannot be deleted."], 422);
        }
        if ($user->signature_path) Storage::disk('public')->delete($user->signature_path);
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    /** Teachers and School Heads belong to a school (one School Head per school); others need an office/unit. */
    private function applySchool(array &$d, ?int $ignoreId = null): ?string
    {
        if (!in_array($d['role'], ['teacher', 'school_head'])) {
            $d['school_id'] = null;
            return blank($d['office'] ?? null) ? 'Please enter the office / unit for this role.' : null;
        }
        if (blank($d['school_id'] ?? null)) return 'Teachers and School Heads must be assigned to a school.';
        $school = School::find($d['school_id']);
        $d['office'] = $school->name;
        if ($d['role'] === 'school_head') {
            $other = User::where('role', 'school_head')->where('school_id', $school->id)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->first();
            if ($other) return "{$school->name} already has a School Head ({$other->name}). Change that account's role or school first.";
        }
        return null;
    }

    /** A newly designated School Head takes over their school's pending teacher requests. */
    private function repointPending(User $user): void
    {
        if ($user->role !== 'school_head' || !$user->school_id) return;
        TravelRequest::where('status', 'pending_recommendation')
            ->whereHas('user', fn($q) => $q->where('role', 'teacher')->where('school_id', $user->school_id))
            ->update(['recommender_id' => $user->id]);
    }
}
