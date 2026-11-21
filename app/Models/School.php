<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = ['name', 'level', 'school_code', 'district'];

    public function users() { return $this->hasMany(User::class); }
    /** The School Head designated to this school. */
    public function head() { return $this->hasOne(User::class)->where('role', 'school_head'); }
}
