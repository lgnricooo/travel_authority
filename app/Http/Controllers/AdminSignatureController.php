<?php
namespace App\Http\Controllers;

use App\Models\User;

/** The designated focal person (administrator) verifies each user's e-signature. */
class AdminSignatureController extends Controller
{
    public function index()
    {
        $users = User::with('verifier', 'school')->whereNotNull('signature_path')
            ->orderByRaw('signature_verified_at is not null')->orderBy('name')->get();
        return view('admin.signatures', compact('users'));
    }

    public function verify(User $user)
    {
        if (!$user->signature_path || !$user->signature_undertaking_at) {
            return response()->json(['message' => 'This user has not accepted the e-signature undertaking. Ask them to re-save their signature.'], 422);
        }
        $user->update(['signature_verified_at' => now(), 'signature_verified_by' => auth()->id()]);
        return response()->json(['message' => "{$user->name}'s e-signature is verified."]);
    }

    public function revoke(User $user)
    {
        $user->update(['signature_verified_at' => null, 'signature_verified_by' => null]);
        return response()->json(['message' => "{$user->name}'s e-signature was revoked. They cannot sign until it is verified again."]);
    }
}
