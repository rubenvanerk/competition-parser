<?php

class Result
{
    private $firstName;
    private $lastName;
    private $yearOfBirth;
    private $times;

    /**
     * Result constructor.
     * @param string $firstName
     * @param string $lastName
     * @param int $yearOfBirth last two digits of the year
     * @param array $times
     */
    public function __construct($firstName, $lastName, $yearOfBirth, $times)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->yearOfBirth = $yearOfBirth;
        $this->times = $times;
    }

    public static function create($firstName, $lastName, $yearOfBirth, $times)
    {
        if($firstName && $lastName && $yearOfBirth && $times) {
            return new Result($firstName, $lastName, $yearOfBirth, $times);
        }
        return null;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return mb_convert_encoding($this->lastName, 'UTF-8', 'ASCII');
;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return mb_convert_encoding($this->firstName, 'UTF-8', 'ASCII');
    }

    /**
     * @return int
     */
    public function getYearOfBirth()
    {
        return $this->yearOfBirth;
    }

    /**
     * @return mixed
     */
    public function getEscapedLastName()
    {
        return str_replace("'", "''", $this->getLastName());
    }

    /**
     * @return mixed
     */
    public function getFirstTime()
    {
        return $this->times[0];
    }
}