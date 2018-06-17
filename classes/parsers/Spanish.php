<?php

class Spanish extends CompetitionParser
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
        if ($this->lineContains($line, $GLOBALS['config']['parser']['spanish']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['spanish']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}:[0-9]{2}:[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['spanish']['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        $name = array();
        preg_match('/[0-9]{4}\s[A-Z]+/', utf8_decode($line), $name);
        print_r(utf8_decode($line) . PHP_EOL);
        var_dump($name);
        sleep(3);
        if($name) {
            $firstName = trim($name[0]);
            return trim($firstName);
        } else {
            preg_match('/,\s?([A-Z\x{0090}-\x{00ff}]+-?\.?\s)+/', utf8_decode($line), $name);
            $firstName = substr($name[0], 1);
            return trim($firstName);
        }
    }

    /**
     * @param string $line
     * @return string
     */
    function getLastNameFromLine($line)
    {
        $name = array();
        preg_match('/\s{2}(\s?[A-Z\x{0090}-\x{00ff}]+-?)+/', utf8_decode($line), $name);
        if($name) {
            $lastName = trim($name[0]);
            return trim($lastName);
        } else {
            preg_match('/(\s?[A-Z\x{0090}-\x{00ff}]+-?)+/', utf8_decode($line), $name);
            $lastName = trim($name[0]);
            return trim($lastName);
        }
    }

    /**
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
        return $this->getFirstNameFromLine($line) . " " . $this->getLastNameFromLine($line);
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
        $i = 0;
        foreach ($times as $time) {
            $times[$i] = $time[5] = ".";
            $i++;
        }
        return $times;
    }
}