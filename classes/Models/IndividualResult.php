<?php namespace CompetitionParser\Classes\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualResult extends Model
{
    protected $table = 'rankings_individualresult';
    public $timestamps = false;

    public $name;
    public $yearOfBirth;
    public $times;
    public $originalLine;
    public $isDq;
    public $isDns;
    public $classification;


    public function splits()
    {
        return $this->hasMany('CompetitionParser\Classes\Models\IndividualResultSplit', 'individual_result_id');
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->name;
        if(ENCODING !== 'UTF-8') $name = mb_convert_encoding($this->name, 'UTF-8', ENCODING);
        return str_replace("'", "''", $name);
    }

    /**
     * @return int
     */
    public function getYearOfBirth()
    {
        return $this->yearOfBirth;
    }

    /**
     * @return int
     */
    public function getYearOfBirthOrNull()
    {
        if($this->yearOfBirth == 'unknown') return "NULL";
        return "'$this->yearOfBirth'";
    }

    /**
     * @return array
     */
    public function getTimes()
    {
        return $this->times;
    }

    /**
     * @return mixed
     */
    public function getFirstTime()
    {
        return $this->times[0];
    }

    /**
     * @return string
     */
    public function getOriginalLine()
    {

        $originalLine = $this->originalLine;
        if(ENCODING !== 'UTF-8') $originalLine = mb_convert_encoding($this->name, 'UTF-8', ENCODING);
        return str_replace("'", "''", $originalLine);
    }

    /**
     * @return bool
     */
    public function isDq()
    {
        return $this->isDq ? 'true' : 'false';
    }

    /**
     * @return mixed
     */
    public function isDns()
    {
        return $this->isDns ? 'true' : 'false';
    }

    /**
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * @return int
     */
    public function getClassification(): int
    {
        return $this->classification;
    }
}