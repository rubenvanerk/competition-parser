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
            return new Result($firstName, $lastName, $yearOfBirth, $times);
        }
        print_r($line . PHP_EOL);
        sleep(1);
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