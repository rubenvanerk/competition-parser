<?php namespace CompetitionParser\Classes\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualResultSplit extends Model
{
    protected $table = 'rankings_individualresultsplit';
    public $timestamps = false;

    public $time;
    public $distance;
    protected $fillable = ['time', 'distance', 'individual_result_id'];
}