<?php

class Rescue2014 extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        define("PARSE_YOB", $GLOBALS['config']['parser'][strtolower(self::class)]['parse_yob']);
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }
    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['rescue2014']['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $GLOBALS['config']['parser']['rescue2014']['genders']['male_signifiers'])) return 1;
        return 0;
    }

    public function getLineType($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['rescue2014']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['rescue2014']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}:[0-9]{2}\,[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['rescue2014']['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $csv = str_getcsv($line);
        return utf8_decode($csv[3] . " " . $csv[2]);
    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $csv = str_getcsv($line);
        return substr($csv[4], 2);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $csv = str_getcsv($line);
        return [str_replace(',', '.', $csv[7])];
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $GLOBALS['config']['parser']['rescue2014']['event_rejectors']);
    }
}