<?php namespace CompetitionParser\Classes\Models;

use Illuminate\Database\Eloquent\Model;

class Nationality extends Model
{
    protected $table = 'rankings_nationality';
    public $timestamps = false;

    public function athletes()
    {
        return $this->belongsToMany('CompetitionParser\Classes\Models\Athlete');
    }
}