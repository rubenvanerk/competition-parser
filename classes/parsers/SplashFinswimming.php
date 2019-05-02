<?php

class SplashFinswimming extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {

        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        
        self::$_instance->config = [
            'event_signifiers' => ['Programmanr'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'result_rejectors' => ['DIS', 'Nederlandse records', 'Splash Meet Manager', '0m:', 'NR Vinzwemmen', 'NLREC'],
            'event_rejectors' => ['4 x '],
            'parse_yob' => 1,
            'disciplines' => [
                1 => ["50m afstand duiken"],
                2 => ["50m bifins"],
                3 => ["100m bifins"],
                4 => ["200m bifins"],
                5 => ["400m bifins"],
                6 => ["50m met vinnen"],
                7 => ["100m met vinnen"],
                8 => ["200m met vinnen"],
                9 => ["400m met vinnen"],
                10 => ["800m met vinnen"],
                11 => ["1500m met vinnen"],
                12 => ["400m perslucht"],
                13 => ["800m perslucht"],
                14 => ["25m afstand duiken"],
                15 => ["25m met vinnen"],
                16 => ["100m perslucht"],
                17 => ["200m perslucht"],
            ],
            'genders' => [
                'male_signifiers' => ['Heren', 'Jongens'],
                'female_signifiers' => ['Dames', 'Meisjes']
            ],
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    public function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']) && strpos($line, 'NG') !== 0;
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        $matches = array();
        preg_match('/(\s?[A-Z]?[a-z\x{0060}-\x{00ff}]+-?)+/', utf8_decode($line), $matches);
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
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $matches = array();
//        preg_match('/(\s?[A-Z]?[a-z\x{0060}-\x{00ff}]+-?)+/', utf8_decode($line), $matches);
        preg_match('/([A-z\x{0060}-\x{00ff}\,?\-]+?\s?)+/', utf8_decode($line), $matches);
        $name = str_replace(',', '', $matches[0]);
        preg_match('/([A-Z]+\s?)+\,\s([A-Z][a-z\x{0060}-\x{00ff}]+\s?)+/', utf8_decode($line), $matches);
//        $nameParts = explode(',', $matches[0]);
//        $name = trim($nameParts[1]) . ' ' . trim($nameParts[0]);


        return trim(utf8_encode($name));
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
        preg_match_all('/[0-9]{0,2}[:]?[0-9]{1,2}[.][0-9]{2}/', $line, $times);
        $times = end($times);
        return [end($times)];
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }
}