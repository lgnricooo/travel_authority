<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['title', 'note', 'group', 'salary_grade', 'sort'];

    /** Text shown in the searchable dropdown. */
    public function label(): string
    {
        return $this->title . ($this->note ? ' — ' . $this->note : '') . ($this->salary_grade ? ' (' . $this->salary_grade . ')' : '');
    }
}
