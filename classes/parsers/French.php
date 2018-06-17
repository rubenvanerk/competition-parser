<?php

class French extends CompetitionParser
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
        if ($this->lineContains($line, $GLOBALS['config']['parser']['french']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['french']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/\"[0-9]{1}'[0-9]{2}\"\"[0-9]{2}\"/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['french']['result_rejectors']);
        return $hasResult && $isValid;
    }


    /**
     * @param string $line
     * @return string
     */
    public function getNameFromLine($line)
    {
        $matches = array();
        preg_match('/(\s?[A-Z][a-z\x{0040}-\x{00ff}]+-?)+/', utf8_decode($line), $matches);
        $name = trim(utf8_encode($matches[0]));

        //put last piece of name at front
        $pieces = explode(' ', $name);
        $firstName = end($pieces);
        array_pop($pieces);
        array_unshift($pieces, $firstName);

        $name = implode(' ', $pieces);

        return $name;
    }


    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\([0-9]{4}\)/', $line, $matches);
        if(isset($matches[0])) {
            $yearOfBirth = $matches[0];
            return substr($yearOfBirth, 3, 2);
        }
        return 'unknown';
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match("/\"[0-9]{1}'[0-9]{2}\"\"[0-9]{2}\"/", $line, $times);
        $time = substr($times[0], 1, 8);
        $time = str_replace('\'', ':', $time);
        $time = str_replace('""', '.', $time);
        return [$time];
    }
}