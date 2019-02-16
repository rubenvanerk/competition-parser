<?php

class WorldGames2017 extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['Ergebnisse'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => [],
            'parse_yob' => 0,
            'disciplines' => [
                1 => ["100m Manikin Carry with Fins"],
                2 => ["50m Manikin Carry"],
                3 => ["200m Obstacle Swim"],
                4 => ["100m Manikin Tow with Fins"],
                5 => ["100m Rescue Medley"],
                6 => ["200m Super Lifesaver"],
                7 => ["50m Obstacle Swim"],
                9 => ["50m Freestyle with Fins"],
                10 => ["50m Manikin Carry (relay leg 3)"],
                12 => ["25m Manikin Carry"],
                14 => ["50m Manikin Carry with Fins (relay leg 4)"],
            ],
            'genders' => [
                'male_signifiers' => ['männlich'],
                'female_signifiers' => ['weiblich']
            ]
        ];
        
        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    public function createUsableLines($lines, $type) 
    {
        $newLines = [];
        $name = '';
        $yearOfBirth = '';
        $time = '';
    
        foreach($lines as $line) {
            print_r($line);
            if(preg_match("/[A-Z\x{00C0}-\x{00DF}]{2,}\s([A-Z][a-z]*[\s\-]?){1,2}/", $line, $matches)) {
                $name = $matches[0];
                print_r($name . PHP_EOL);
                sleep(1);
            } elseif (preg_match('/[0-9]?:?[0-9]{2}\.[0-9]{2}/', $line, $matches)) {
                print_r($line . PHP_EOL);
            }
        }
    }

    protected function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
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
        return utf8_decode($this->getFirstNameFromLine($line) . " " . $this->getLastNameFromLine($line));
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
        return [$times[0]];
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }
}