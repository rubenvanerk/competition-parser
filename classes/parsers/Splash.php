<?php

class Splash extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {

        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }
        
        self::$_instance->config = [
            'event_signifiers' => ['Programmanr', 'Event'],
            'event_designifiers' => ['DISKWALIFICATIE CODES'], // signifies a line is definitely not an event line
            'result_rejectors' => ['DSQ', 'disq', 'DNS', 'DC 20', 'DC 1', 'Selectietijd', 'Splash Meet Manager', 'DNF', 'BR CAD', 'BR OPEN', 'BR JUN', 'BR M', 'BR BEN', 'BR MIN', 'World Record', 'World Master Record', 'Record'],
            'event_rejectors' => ['Jongens', 'Meisjes'],
            'parse_yob' => 1,
            'disciplines' => [
                 1 => ["100m manikin carry with fins", "100m popduiken met zwemvliezen", "100 m. remolque de maniquí", "100m manikin (ring) carry with fins", "100 m Manikin Carry with Fins", "100m mannequin palmes", "100 manikin carry with fins", "A2-Popredden met vinnen", "100m Popredden met vinnen"],
                 2 => ["50m manikin carry", "50m popduiken", "50 m. remolque de maniquí", "50m Mannequin", "50 manikin carry", "A4-Popredden", "50m Popredden"],
                 3 => ["200m obstacle swim", "200m hinderniszwemmen", "200 m. natación con obstáculos", "200 m Obstacle Swim", "200m Obstacles", "A1-Hinderniszwemmen"],
                 4 => ["100m manikin tow with fins", "100m lifesaver", "100 m. socorrista", "100 m Manikin Tow with Fins", "100 manikin tow with fins", "100m Manikin Tow with Fins", "A5-Lifesaver"],
                 5 => ["100m rescue medley", "100m reddingswisselslag", "100 m. combinada de salvamento", "100 m Rescue Medley", "100m Combiné", "A3-Reddingscombiné", "100m Reddingscombiné"],
                 6 => ["200m superlifesaver", "200 m. supersocorrista", "200m super lifesaver", "200 m Super Lifesaver", "A6-Super Lifesaver"],
                 7 => ["50 m obstacle swim"],
                 8 => ["50 m free style"],
                 9 => ["50 m freestyle with fins"],
                 10 => ["50 m manikin"],
                 11 => ["50 m slepen"],
                 12 => ["25 m pop"],
                 13 => ["50 m vrij met torpedo"],
                 14 => ["50 m pop met vliezen"],
                18 => ["100m Obstacle Swim"],
            ],
            'genders' => [
                'male_signifiers' => ['Men', 'Heren', 'Boys'],
                'female_signifiers' => ['Women', 'Dames']
            ],
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    public function hasValidResult($line)
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
        preg_match('/(*UTF8)(\s?[A-Z]?[a-z\x{00DF}-\x{00ff}]+-?)+/', $line, $matches);
        return trim($matches[0]);
    }

    /**
     * @param string $line
     * @return string
     */
    function getLastNameFromLine($line)
    {
        $matches = array();
        preg_match('/(*UTF8)(\s\'?[a-z]+)*((\s?[A-Z\x{00C0}-\x{00DF}]{2,}\s?)+([\']\w+\s)?-?)+/', $line, $matches);
        return trim($matches[0]);
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
        $yearOfBirth = trim($matches[0]);
        $yearOfBirth += ($yearOfBirth < 19 ? 2000 : 1900);
        return $yearOfBirth;
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