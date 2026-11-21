<?php
namespace App\Http\Controllers;

use App\Models\TravelRequest;

/** Public page so anyone holding a printed copy can check it against the system. */
class VerifyController extends Controller
{
    public function show(string $code)
    {
        $t = TravelRequest::with('logs.user')->where('verification_code', strtoupper($code))->first();
        return view('verify', ['t' => $t, 'ok' => $t?->integrityOk()]);
    }
}
