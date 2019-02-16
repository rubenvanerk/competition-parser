<?php

class Rescue2014 extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['Heat', 'Final'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => ['00:00,00'],
            'parse_yob' => 0,
            'disciplines' => [
                1 => ["100 Manikin Carry with Fins"],
                2 => ["50 Manikin Carry"],
                3 => ["200 Obstacle Swim"],
                4 => ["100 Manikin tow with fins"],
                5 => ["100 Rescue medley"],
                6 => ["200 SLS"],
                7 => ["50m Obstacle Swim"],
                9 => ["50m Freestyle with Fins"],
                10 => ["50m Manikin Carry (relay leg 3)"],
                12 => ["25m Manikin Carry"],
                14 => ["50m Manikin Carry with Fins (relay leg 4)"],
            ],
            'genders' => [
                'male_signifiers' => ['Men'],
                'female_signifiers' => ['Women']
            ]
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    protected function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}:[0-9]{2}\,[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
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
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }
}