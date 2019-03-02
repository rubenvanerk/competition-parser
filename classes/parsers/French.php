<?php

class French extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['Dames', 'Messieurs'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'result_rejectors' => ['F/DQ'],
            'disciplines' => [
                1 => ["100 m Man. Palmes"],
                2 => ["50 m Man."],
                3 => ["200 m Obstacles"],
                4 => ["100 m Bouée Tube"],
                5 => ["100 m Combiné"],
                6 => ["200 m SLS"],
                7 => ["50 m obstacle swim nope"],
                8 => ["50 m free style nope"],
                9 => ["50 m freestyle with nope"],
                10 => ["50 m nope"],
                11 => ["50 m nope"],
                12 => ["25 m nope"],
                13 => ["50 m nope met torpedo"],
                14 => ["50 m nope met vliezen"],
            ],
            'genders' => [
                'male_signifiers' => 'Messieurs',
                'female_signifiers' => 'Dames'
            ],
            'parse_yob' => 1
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    public function hasValidResult($line)
    {
        $hasResult = preg_match("/\"[0-9]{1}'[0-9]{2}\"\"[0-9]{2}\"/", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
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

    public function shouldIncludeEvent($line)
    {
        return true;
    }


}