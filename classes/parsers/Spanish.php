<?php

class Splash extends CompetitionParser
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
        if ($this->lineContains($line, $GLOBALS['config']['parser']['splash']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['splash']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['splash']['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        $matches = array();
        preg_match('/(\s?[A-Z][a-z\x{0040}-\x{00ff}]+-?)+/', utf8_decode($line), $matches);
        return trim(utf8_encode($matches[0]));
    }

    /**
     * @param string $line
     * @return string
     */
    function getLastNameFromLine($line)
    {
        $matches = array();
        preg_match('/(\s\'?[a-z]+)*((\s?[A-Z\x{00C0}-\x{00DF}]{2,}\s?)+([\']\w+\s)?-?)+/', utf8_decode($line), $matches);
        return trim(utf8_encode($matches[0]));
    }


    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{2}\s/', $line, $matches);
        return trim($matches[0]);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match('/[0-9]{0,2}[:]?[0-9]{1,2}[.][0-9]{2}/', $line, $times);
        return $times;
    }
}