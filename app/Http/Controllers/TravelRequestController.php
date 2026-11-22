<?php
namespace App\Http\Controllers;

use App\Models\TravelRequest;
use App\Models\User;
use App\Support\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TravelRequestController extends Controller
{
    public function index()
    {
        $u = auth()->user();
        $mine = TravelRequest::with('recommender', 'approver')->where('user_id', $u->id)->latest()->get();
        $forAction = TravelRequest::with('user', 'recommender', 'approver')
            ->where(fn($q) => $q
                ->where(fn($x) => $x->where('status', 'pending_recommendation')->where('recommender_id', $u->id))
                ->orWhere(fn($x) => $x->where('status', 'pending_approval')->where('approver_id', $u->id)))
            ->latest()->get();
        return view('requests.index', compact('mine', 'forAction'));
    }

    public function store(Request $r)
    {
        $u = auth()->user();
        $d = $r->validate([
            'purpose' => 'required|string|max:1000',
            'host_of_activity' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'date_from' => 'required|date|after_or_equal:today',
            'date_to' => 'required|date|after_or_equal:date_from',
            'fund_source' => 'required|string|max:255',
            'memo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:4096',
        ], [
            'memo.required' => 'Please attach the supporting memo (the purpose of travel must be supported by attachments).',
            'memo.mimes' => 'The memo must be a PDF, JPG or PNG file.',
            'memo.max' => 'The memo must not be larger than 4 MB.',
        ]);
        if (!$u->canRequest()) return $this->fail('Your account role cannot file a travel authority.');
        if ($p = $this->signerProblem($r, $u)) return $this->fail($p);

        $approver = User::where('role', 'sds')->first();
        $recommender = $u->resolveRecommender();
        if (!$recommender) {
            return $this->fail($u->role === 'teacher'
                ? ($u->school_id ? 'Your school has no School Head assigned yet. Please contact the administrator.'
                                 : 'You are not assigned to a school yet. Please contact the administrator.')
                : 'No recommending officer is assigned to you. Please contact the administrator.');
        }
        if (!$approver) return $this->fail('No SDS account exists yet. Please contact the administrator.');

        $t = TravelRequest::create([
            'user_id' => $u->id,
            'recommender_id' => $recommender->id,
            'approver_id' => $approver->id,
            'purpose' => $d['purpose'], 'host_of_activity' => $d['host_of_activity'], 'destination' => $d['destination'],
            'date_from' => $d['date_from'], 'date_to' => $d['date_to'], 'fund_source' => $d['fund_source'],
            'memo_path' => $r->hasFile('memo') ? $r->file('memo')->store('memos', 's3') : null,
            'verification_code' => Str::upper(Str::random(10)),
        ]);
        $t->update([
            'tracking_no' => sprintf('TA-%s-%05d', now()->year, $t->id),
            'requester_signature' => $t->snapshotSignature($u, 'requester'),
        ]);
        $t->setParty('requester', $u);
        $t->recordEvent($u, 'requested', $t->requester_signature, $r);

        return response()->json(['message' => 'Travel authority submitted.', 'redirect' => route('requests.show', $t)]);
    }

    public function show(TravelRequest $travelRequest)
    {
        abort_unless($travelRequest->canView(auth()->user()), 403);
        return view('requests.show', ['t' => $travelRequest->load('user', 'recommender', 'approver', 'logs.user'), 'u' => auth()->user()]);
    }

    public function action(Request $r, TravelRequest $travelRequest)
    {
        $u = auth()->user(); $t = $travelRequest;
        $d = $r->validate(['decision' => 'required|in:recommend,approve,reject', 'remarks' => 'nullable|string|max:500']);
        $signing = $d['decision'] !== 'reject';
        if (!$signing && blank($d['remarks'] ?? null)) return $this->fail('Please state the reason for rejection.');
        if ($signing && ($p = $this->signerProblem($r, $u))) return $this->fail($p);

        if ($t->status === 'pending_recommendation' && (int) $t->recommender_id === $u->id && $d['decision'] !== 'approve') {
            if (!$signing) {
                $t->update(['status' => 'rejected', 'recommender_remarks' => $d['remarks'], 'decided_at' => now()]);
                $t->setParty('recommender', $u);
                $t->recordEvent($u, 'rejected', null, $r);
            } else {
                $t->update(['status' => 'pending_approval', 'recommender_remarks' => $d['remarks'] ?? null,
                    'recommended_at' => now(), 'recommender_signature' => $t->snapshotSignature($u, 'recommender')]);
                $t->setParty('recommender', $u);
                $t->recordEvent($u, 'recommended', $t->recommender_signature, $r);
            }
        } elseif ($t->status === 'pending_approval' && (int) $t->approver_id === $u->id && $d['decision'] !== 'recommend') {
            if (!$signing) {
                $t->update(['status' => 'rejected', 'approver_remarks' => $d['remarks'], 'decided_at' => now()]);
                $t->setParty('approver', $u);
                $t->recordEvent($u, 'rejected', null, $r);
            } else {
                $t->update(['status' => 'approved', 'approver_remarks' => $d['remarks'] ?? null,
                    'decided_at' => now(), 'approver_signature' => $t->snapshotSignature($u, 'approver')]);
                $t->setParty('approver', $u);
                $t->recordEvent($u, 'approved', $t->approver_signature, $r);
            }
        } else {
            return $this->fail('You cannot act on this request at its current stage.', 403);
        }
        return response()->json(['message' => 'Request updated: ' . $t->fresh()->statusLabel(), 'redirect' => route('requests.index')]);
    }

    /** Streams the memo only to people allowed to see the request. */
    public function memo(Request $r, TravelRequest $travelRequest)
    {
        abort_unless($travelRequest->canView(auth()->user()), 403);
        abort_unless($travelRequest->memo_path, 404);

        if (Storage::disk('s3')->exists($travelRequest->memo_path)) {
            $name = $travelRequest->tracking_no . '-memo.' . pathinfo($travelRequest->memo_path, PATHINFO_EXTENSION);
            return $r->boolean('download')
                ? Storage::disk('s3')->download($travelRequest->memo_path, $name)
                : Storage::disk('s3')->response($travelRequest->memo_path, $name);
        }

        abort(404);
    }

    /** A signatory's e-signature image as frozen on this request (never a public URL). */
    public function signature(TravelRequest $travelRequest, string $who)
    {
        abort_unless(in_array($who, ['requester', 'recommender', 'approver']), 404);
        abort_unless($travelRequest->canView(auth()->user()), 403);
        return Files::response($travelRequest->{$who . '_signature'}, $who . '-signature.png');
    }

    public function print(TravelRequest $travelRequest)
    {
        abort_unless($travelRequest->canView(auth()->user()), 403);
        abort_unless($travelRequest->status === 'approved', 403, 'Only approved requests can be printed.');
        return view('requests.print', ['t' => $travelRequest->load('user', 'recommender', 'approver')]);
    }

    /**
     * COA Circular 2021-006 controls before an e-signature may be affixed:
     * the signature must be verified, the signer must affirm intent, and must re-authenticate.
     */
    private function signerProblem(Request $r, User $u): ?string
    {
        if (!$u->signature_path) return 'Please set up your e-signature first (My Profile).';
        if (!$u->signature_verified_at) return 'Your e-signature is still awaiting verification by the administrator (focal person). You cannot sign yet.';
        if (!$r->boolean('affirm')) return 'Please tick the affirmation to sign this document.';
        if (!Hash::check((string) $r->input('sign_password'), $u->password)) return 'Incorrect password. Signing requires your password.';
        return null;
    }

    private function storeMemo($file): string
    {
        $path = 'memos/' . Str::random(40) . '.' . strtolower($file->extension());
        Files::put($path, file_get_contents($file->getRealPath()));
        return $path;
    }

    private function fail(string $m, int $code = 422) { return response()->json(['message' => $m], $code); }
}
