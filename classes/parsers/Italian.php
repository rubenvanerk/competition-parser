<?php

class Italian extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    public function getLineType($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['italian']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['italian']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}.[0-9]{2}/", $line);
        $resultIsFromRoundUp = preg_match("/[0-9] [0-9] [0-9]{1,2}./", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['italian']['result_rejectors']);
        return $hasResult && $isValid && $resultIsFromRoundUp;
    }

    /**
     * @param string $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $name = array();
        preg_match('/[A-Z\x{00C0}-\x{00D0}]{2,}/', utf8_decode($line), $name);
        $lastName = implode(' ', $name);
        preg_match('/[A-Z\x{00C0}-\x{00D0}]{1}[a-z]+/', utf8_decode($line), $name);
        $firstName = implode(' ', $name);

        return $firstName . " " . $lastName;

    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{4}\s/', $line, $matches);
        $yearOfBirth = $matches[0];
        return substr($yearOfBirth, 3, 2);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match_all('/[0-9]\'[0-9]{2}.[0-9]{2}/', $line, $times);
        if(count($times[0])) {
            if(count($times[0]) > 1) $times[0] = array_slice($times[0], 0, 1);
            return str_replace("'", ":", $times[0]);
        } else {
            preg_match_all('/[0-9]{2}\.[0-9]{2}/', $line, $times);
            if(count($times[0]) > 1) $times[0] = array_slice($times[0], 0, 1);
            return str_replace("'", ":", $times[0]);
        }
    }

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['italian']['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $GLOBALS['config']['parser']['italian']['genders']['male_signifiers'])) return 1;
        return 0;
    }
}