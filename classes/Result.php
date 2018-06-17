<?php

class Result
{
    private $name;
    private $yearOfBirth;
    private $times;

    /**
     * Result constructor.
     * @param string $name
     * @param int $yearOfBirth last two digits of the year
     * @param array $times
     */
    public function __construct($name, $yearOfBirth, $times)
    {
        $this->name = $name;
        $this->yearOfBirth = $yearOfBirth;
        $this->times = $times;
    }

    public static function create($name, $yearOfBirth, $times)
    {
        if($name && $yearOfBirth && $times) {
            return new Result($name, $yearOfBirth, $times);
        }
        return null;
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
     * @return mixed
     */
    public function getFirstTime()
    {
        return $this->times[0];
    }
}