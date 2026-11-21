<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TravelRequest extends Model
{
    protected $guarded = [];
    protected $casts = [
        'date_from' => 'date', 'date_to' => 'date', 'recommended_at' => 'datetime',
        'decided_at' => 'datetime', 'snapshot' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function ($t) {
            $t->public_id ??= strtolower((string) \Illuminate\Support\Str::ulid());
        });
    }

    /** URLs use the random public_id (e.g. /requests/01hx...) instead of the numeric id. */
    public function getRouteKeyName(): string { return 'public_id'; }

    public function user() { return $this->belongsTo(User::class); }
    public function recommender() { return $this->belongsTo(User::class, 'recommender_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approver_id'); }
    public function logs() { return $this->hasMany(SignatureLog::class)->orderBy('id'); }

    public function statusLabel(): string {
        return ['pending_recommendation' => 'For Recommendation', 'pending_approval' => 'For Approval',
                'approved' => 'Approved', 'rejected' => 'Rejected'][$this->status] ?? $this->status;
    }
    public function statusColor(): string {
        return ['pending_recommendation' => 'warning', 'pending_approval' => 'info',
                'approved' => 'success', 'rejected' => 'danger'][$this->status] ?? 'secondary';
    }

    /** Who the request is currently waiting on (null once approved/rejected). */
    public function pendingWith(): ?User {
        return match ($this->status) {
            'pending_recommendation' => $this->recommender,
            'pending_approval' => $this->approver,
            default => null,
        };
    }
    /** Who rejected it, judging by whose remarks were recorded. */
    public function rejectedBy(): ?User {
        if ($this->status !== 'rejected') return null;
        return filled($this->approver_remarks) ? $this->approver : $this->recommender;
    }
    /** Furthest stage reached: 1 = filed, 2 = recommended, 3 = approved. */
    public function stage(): int {
        return match ($this->status) {
            'pending_recommendation' => 1,
            'pending_approval' => 2,
            'approved' => 3,
            default => filled($this->approver_remarks) ? 2 : 1, // rejected
        };
    }

    /** e.g. "November 25-27, 2026", "November 30 - December 2, 2026" or "November 25, 2026". */
    public function inclusiveDates(): string {
        $a = $this->date_from; $b = $this->date_to;
        if ($a->isSameDay($b)) return $a->format('F j, Y');
        if ($a->format('Y-m') === $b->format('Y-m')) return $a->format('F j') . '-' . $b->format('j, Y');
        if ($a->year === $b->year) return $a->format('F j') . ' - ' . $b->format('F j, Y');
        return $a->format('F j, Y') . ' - ' . $b->format('F j, Y');
    }

    public function canView(User $u): bool {
        return in_array($u->id, [$this->user_id, $this->recommender_id, $this->approver_id]) || $u->role === 'admin';
    }
    public function memoUrl(bool $download = false): ?string {
        return $this->memo_path ? route('requests.memo', ['travelRequest' => $this] + ($download ? ['download' => 1] : [])) : null;
    }
    public function memoIsPdf(): bool { return $this->memo_path && str_ends_with(strtolower($this->memo_path), '.pdf'); }

    /** Freeze a copy of the signer's e-signature at the time of signing. */
    public function snapshotSignature(User $u, string $tag): string {
        $p = "signatures/req{$this->id}_{$tag}.png";
        Storage::disk('s3')->put($p, Storage::disk('s3')->get($u->signature_path));
        return $p;
    }

    // ---- Signatory details frozen at signing time (so old forms never change) ----
    public function party(string $who): array {
        if (isset($this->snapshot[$who])) return $this->snapshot[$who];
        $u = ['requester' => $this->user, 'recommender' => $this->recommender, 'approver' => $this->approver][$who] ?? null;
        return ['name' => $u?->name, 'position' => $u?->position, 'office' => $u?->office];
    }
    public function setParty(string $who, User $u): void {
        $s = $this->snapshot ?? [];
        $s[$who] = ['name' => $u->name, 'position' => $u->position, 'office' => $u->office];
        $this->snapshot = $s;
        $this->save();
    }

    // ---- Integrity: a hash chain over the document content at every signing event ----
    public function memoSha256(): ?string {
        if (!$this->memo_path) return null;
        if (Storage::disk('s3')->exists($this->memo_path)) {
            return hash('sha256', Storage::disk('s3')->get($this->memo_path));
        }
        return null;
    }
    private function payload(array $parties): array {
        $p = [];
        foreach ($parties as $k) {
            $x = $this->party($k);
            $p[$k] = ['name' => $x['name'] ?? null, 'position' => $x['position'] ?? null, 'office' => $x['office'] ?? null];
        }
        return [
            'tracking_no' => $this->tracking_no, 'purpose' => $this->purpose, 'host_of_activity' => $this->host_of_activity,
            'destination' => $this->destination, 'date_from' => $this->date_from->toDateString(),
            'date_to' => $this->date_to->toDateString(), 'fund_source' => $this->fund_source,
            'memo_sha256' => $this->memoSha256(), 'parties' => $p,
        ];
    }
    private function hashFor(string $event, array $parties, ?string $prev): string {
        return hash('sha256', json_encode($this->payload($parties) + ['event' => $event, 'prev' => $prev],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Append a signing/rejection event to the audit trail. */
    public function recordEvent(User $u, string $event, ?string $sigPath, Request $r): void {
        $parties = match ($event) {
            'requested' => ['requester'],
            'recommended' => ['requester', 'recommender'],
            'approved' => ['requester', 'recommender', 'approver'],
            default => array_values(array_intersect(['requester', 'recommender', 'approver'], array_keys($this->snapshot ?? []))),
        };
        $prev = SignatureLog::where('travel_request_id', $this->id)->orderByDesc('id')->value('document_hash');
        SignatureLog::create([
            'travel_request_id' => $this->id, 'user_id' => $u->id, 'event' => $event, 'parties' => $parties,
            'signature_sha256' => $sigPath && Storage::disk('s3')->exists($sigPath)
                ? hash('sha256', Storage::disk('s3')->get($sigPath)) : null,
            'document_hash' => $this->hashFor($event, $parties, $prev),
            'ip_address' => $r->ip(), 'user_agent' => mb_substr((string) $r->userAgent(), 0, 255), 'signed_at' => now(),
        ]);
    }

    /** Recompute every hash in the chain; false if the document, memo or log was altered. */
    public function integrityOk(): bool {
        $prev = null;
        foreach ($this->logs()->get() as $log) {
            if ($log->document_hash !== $this->hashFor($log->event, $log->parties, $prev)) return false;
            $prev = $log->document_hash;
        }
        return true;
    }
}
