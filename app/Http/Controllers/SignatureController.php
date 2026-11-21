<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    public function store(Request $r)
    {
        $r->validate(['signature' => 'required|string', 'undertaking' => 'accepted']);
        if (!preg_match('#^data:image/png;base64,#', $r->signature)) {
            return response()->json(['message' => 'Invalid signature image.'], 422);
        }
        $bin = base64_decode(substr($r->signature, strpos($r->signature, ',') + 1), true);
        if ($bin === false || strlen($bin) > 500000 || @getimagesizefromstring($bin) === false) {
            return response()->json(['message' => 'Invalid or too large signature.'], 422);
        }
        $u = auth()->user();
        $path = "signatures/user{$u->id}.png";
        Storage::disk('public')->put($path, $bin);
        // A new or changed signature must be verified again by the focal person
        $u->update([
            'signature_path' => $path,
            'signature_undertaking_at' => now(),
            'signature_verified_at' => null,
            'signature_verified_by' => null,
        ]);
        return response()->json(['message' => 'E-signature saved. It will be usable once the administrator verifies it.']);
    }
}
