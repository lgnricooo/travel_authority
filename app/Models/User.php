<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'admin' => 'Administrator',
        'teacher' => 'Teacher / School Personnel',
        'school_head' => 'School Head',
        'unit_personnel' => 'SDO Personnel',
        'unit_head' => 'SDO Unit Head',
        'asds' => 'Assistant Schools Division Superintendent',
        'sds' => 'Schools Division Superintendent',
    ];

    // Which roles may file a request (approver is always the SDS)
    // teacher -> School Head | school_head -> ASDS | unit_personnel -> Unit Head | unit_head -> ASDS
    public const RECOMMENDER_ROLE = [
        'teacher' => 'school_head',
        'school_head' => 'asds',
        'unit_personnel' => 'unit_head',
        'unit_head' => 'asds',
    ];

    protected $fillable = ['name','email','password','role','position','office','recommender_id','signature_path','school_id','position_id','signature_undertaking_at','signature_verified_at','signature_verified_by'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['signature_undertaking_at' => 'datetime', 'signature_verified_at' => 'datetime'];

    public function recommender() { return $this->belongsTo(User::class, 'recommender_id'); }
    public function positionRef() { return $this->belongsTo(Position::class, 'position_id'); }
    public function verifier() { return $this->belongsTo(User::class, 'signature_verified_by'); }
    public function school() { return $this->belongsTo(School::class); }

    /**
     * Who recommends this user's travel requests.
     * Teachers/school personnel -> the School Head designated to their school (automatic).
     * Everyone else -> the recommending officer assigned by the admin.
     */
    public function resolveRecommender(): ?User
    {
        if ($this->role === 'teacher') {
            return $this->school_id
                ? User::where('role', 'school_head')->where('school_id', $this->school_id)->first()
                : null;
        }
        return $this->recommender;
    }
    public function roleLabel(): string { return self::ROLES[$this->role] ?? $this->role; }
    public function canRequest(): bool { return isset(self::RECOMMENDER_ROLE[$this->role]); }
}
