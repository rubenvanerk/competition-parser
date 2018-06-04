<?php

class Result
{
    private $firstName;
    private $lastName;
    private $yearOfBirth;
    private $times;
    private $originalLine;

    /**
     * Result constructor.
     * @param string $firstName
     * @param string $lastName
     * @param int $yearOfBirth last two digits of the year
     * @param array $times
     * @param $line
     */
    public function __construct($firstName, $lastName, $yearOfBirth, $times, $line)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->yearOfBirth = $yearOfBirth;
        $this->times = $times;
        $this->originalLine = $line;
    }

    /**
     * @param $line
     * @return null|Result
     */
    public static function createFromLine($line)
    {
        $firstName = getFirstNameFromLine($line);
        $lastName = getLastNameFromLine($line);
        $yearOfBirth = getYearOfBirthFromLine($line);
        $times = getTimesFromLine($line);
        if($firstName && $lastName && $yearOfBirth && $times) {
            return new Result($firstName, $lastName, $yearOfBirth, $times, $line);
        }
        var_dump($line);
        sleep(2);
        return null;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
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