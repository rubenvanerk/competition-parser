<?php

class Italian extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['Donne', 'Uomini'],
            'event_designifiers' => ['Es.', 'Ragazzi'], // signifies a line is definitely not an event line
            'result_rejectors' => ['F/DQ'],
            'disciplines' => [
                1 => ["100m Manikin Carry Fins", "100m Manichino pinne - "],
                2 => ["50m Manikin Carry", "50m Trasporto manichino"],
                3 => ["200m Obstacle Swim", "200m Nuoto con ostacoli"],
                4 => ["100m Manikin Tow Fins", "100m Manich pinne torpedo"],
                5 => ["100m Rescue Medley", "100m Percorso misto"],
                6 => ["200m Super Lifesaver", "200m Super Lifesaver"],
                7 => ["50m Nuoto con ostacoli"],
                8 => ["50 m free style"],
                9 => ["50 m freestyle with fins", "50m Pinne"],
                10 => ["50m Trasp manichino acqua"],
                11 => ["50 m slepen"],
                12 => ["25 m pop"],
                13 => ["50 m vrij met torpedo"],
                14 => ["50m Trasp man pinne acqua"],
            ],
            'genders' => [
                'male_signifiers' => ['Uomini'],
                'female_signifiers' => ['Donne']
            ],
            'parse_yob' => 1
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    protected function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}.[0-9]{2}/", $line);
        $resultIsFromRoundUp = preg_match("/[0-9] [0-9] [0-9]{1,2}./", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        return $hasResult && $isValid && $resultIsFromRoundUp;
    }

    /**
     * @param string $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $name = array();
        preg_match('/[A-Z\x{00C0}-\x{00D0}]{2,}/', utf8_decode($line), $name);
        $lastName = implode(' ', $name);
        preg_match('/[A-Z\x{00C0}-\x{00D0}]{1}[a-z]+/', utf8_decode($line), $name);
        $firstName = implode(' ', $name);

        return $firstName . " " . $lastName;

    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{4}\s/', $line, $matches);
        $yearOfBirth = $matches[0];
        return substr($yearOfBirth, 3, 2);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match_all('/[0-9]\'[0-9]{2}.[0-9]{2}/', $line, $times);
        if (count($times[0])) {
            if (count($times[0]) > 1) $times[0] = array_slice($times[0], 0, 1);
            return str_replace("'", ":", $times[0]);
        } else {
            preg_match_all('/[0-9]{2}\.[0-9]{2}/', $line, $times);
            if (count($times[0]) > 1) $times[0] = array_slice($times[0], 0, 1);
            return str_replace("'", ":", $times[0]);
        }
    }

    protected function shouldIncludeEvent($line)
    {
        return true;
    }


}