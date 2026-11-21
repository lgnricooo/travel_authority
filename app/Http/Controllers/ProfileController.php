<?php
namespace App\Http\Controllers;

use App\Models\{Position, School, TravelRequest, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
            'positions' => Position::orderBy('sort')->get(),
            'schools' => School::orderBy('name')->get(),
        ]);
    }

    /** Name, email, position and school/office. */
    public function update(Request $r)
    {
        $user = auth()->user();
        $schoolBased = in_array($user->role, ['teacher', 'school_head']);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'position_id' => 'required|exists:positions,id',
        ];
        $rules += $schoolBased ? ['school_id' => 'required|exists:schools,id'] : ['office' => 'required|string|max:255'];
        $d = $r->validate($rules);

        // Changing the login email needs the current password
        if (strtolower($d['email']) !== strtolower($user->email)) {
            $r->validate(['current_password' => 'required|current_password']);
        }

        $d['position'] = Position::find($d['position_id'])->title; // printed on the travel authority

        if ($schoolBased) {
            $school = School::find($d['school_id']);
            if ($user->role === 'school_head') {
                $other = User::where('role', 'school_head')->where('school_id', $school->id)->where('id', '!=', $user->id)->first();
                if ($other) {
                    return response()->json(['message' => "{$school->name} already has a School Head ({$other->name}). Please contact the administrator."], 422);
                }
            }
            $d['office'] = $school->name;
        }

        $user->update($d);

        // A School Head now designated to a school takes over its pending teacher requests
        if ($user->role === 'school_head' && $user->school_id) {
            TravelRequest::where('status', 'pending_recommendation')
                ->whereHas('user', fn($q) => $q->where('role', 'teacher')->where('school_id', $user->school_id))
                ->update(['recommender_id' => $user->id]);
        }
        return response()->json(['message' => 'Profile updated.']);
    }

    public function password(Request $r)
    {
        $r->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);
        auth()->user()->update(['password' => Hash::make($r->password)]);
        return response()->json(['message' => 'Password changed.']);
    }
}
