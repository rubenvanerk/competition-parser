<?php

class German extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['over all heats'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'result_rejectors' => ['WR:', 'd.n.s.', 'DC '],
            'disciplines' => [
                1 => ["100m manikin carry with fins", "100m popduiken met zwemvliezen", "100 m. remolque de maniquí", "100m manikin (ring) carry with fins", "100 m Manikin Carry with Fins", "100m mannequin palmes", "100 manikin carry with fins"],
                2 => ["50m manikin carry", "50m popduiken", "50 m. remolque de maniquí", "50m Mannequin", "50 manikin carry", "50 m manikin"],
                3 => ["200m obstacle swim", "200m hinderniszwemmen", "200 m. natación con obstáculos", "200 m Obstacle Swim", "200m Obstacles"],
                4 => ["100m manikin tow with fins", "100m lifesaver", "100 m. socorrista", "100 m Manikin Tow with Fins", "100 manikin tow with fins"],
                5 => ["100m rescue medley", "100m reddingswisselslag", "100 m. combinada de salvamento", "100 m Rescue Medley", "100m Combiné"],
                6 => ["200m superlifesaver", "200 m. supersocorrista", "200m super lifesaver", "200 m Super Lifesaver"],
                7 => ["50 m obstacle swim"],
                8 => ["50 m free style"],
                9 => ["50 m freestyle with fins"],
                10 => ["50 m manikin nope this is relay"],
                11 => ["50 m slepen"],
                12 => ["25 m pop"],
                13 => ["50 m vrij met torpedo"],
                14 => ["50 m pop met vliezen"],
            ],
            'genders' => [
                'male_signifiers' => ['Men'],
                'female_signifiers' => ['Women']
            ],
            'parse_yob' => 1,
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }


    public function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2},[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        $name = array();
        preg_match('/(\s?[A-Za-z\x{0040}-\x{00ff}]+-?\'?)+\s?,\s([A-Za-z\x{0040}-\x{00ff}]+-?)+/', $line, $name);
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
        preg_match('/(\s?[A-Za-z\x{0040}-\x{00ff}]+-?\'?)+\s?,\s([A-Za-z\x{0040}-\x{00ff}]+-?)+/', $line, $name);
        $name = trim($name[0]);
        $lastName = substr($name, 0, strpos($name, ','));
        return $lastName;
    }

    /**
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
//        $line = str_replace('   ,', ',', $line);
        $line = preg_replace('!\s+!', ' ', $line);
        return $this->getFirstNameFromLine($line) . " " . $this->getLastNameFromLine($line);
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

    public function shouldIncludeEvent($line)
    {
        return true;
    }
}