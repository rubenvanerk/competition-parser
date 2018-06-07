<?php

class German extends CompetitionParser
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
        if ($this->lineContains($line, $GLOBALS['config']['parser']['german']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['german']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2},[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['german']['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        $name = array();
        preg_match('/(\s?[A-Za-z\x{0040}-\x{00ff}]+-?)+\s?,\s([A-Za-z\x{00e0}-\x{00ff}]+-?)+/', utf8_decode($line), $name);
        $name = trim($name[0]);
        $firstName = substr($name, strpos($name, ',') + 1);
        return trim($firstName);
    }

    /**
     * @param string $line
     * @return string
     */
    function getLastNameFromLine($line)
    {
        $name = array();
        preg_match('/(\s?[A-Za-z\x{0040}-\x{00ff}]+-?)+\s?,\s([A-Za-z\x{00e0}-\x{00ff}]+-?)+/', utf8_decode($line), $name);
        $name = trim($name[0]);
        $lastName = substr($name, 0, strpos($name, ','));
        return $lastName;
    }


    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{4}\s/', $line, $matches);
        return substr(trim($matches[0]), 2);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match('/[0-9]{1}:[0-9]{2}[,][0-9]{2}/', $line, $times);
        $i = 0;
        foreach ($times as $time) {
            $times[$i] = str_replace(',', '.', $time);
            $i++;
        }
        return isset($times[0]) ? $times : [];
    }
}