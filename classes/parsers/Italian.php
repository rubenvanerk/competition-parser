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
            'event_signifiers' => ['Donne', 'Uomini', 'Assoluti'],
            'event_designifiers' => ['Es.', 'Ragazzi', 'FEDERAZIONE'], // signifies a line is definitely not an event line
            'event_rejectors' => ['Staffetta', 'Serie'],
            'result_rejectors' => ['F/DQ', 'Mondiale', 'Europeo', 'FEDERAZIONE', 'Italiano'],
            'disciplines' => [
                1 => ["100m Manikin Carry Fins", "100m Manichino pinne - ", "Manichino pinne mt.100"],
                2 => ["50m Manikin Carry", "50m Trasporto manichino", "Trasporto manichino"],
                3 => ["200m Obstacle Swim", "200m Nuoto con ostacoli", "Nuoto ostacoli mt.200"],
                4 => ["100m Manikin Tow Fins", "100m Manich pinne torpedo", "Manichino pinne e torpedo"],
                5 => ["100m Rescue Medley", "100m Percorso misto", "Percorso misto mt.100"],
                6 => ["200m Super Lifesaver", "200m Super Lifesaver", "Super lifesaver"],
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
                'male_signifiers' => ['Uomini', 'Maschile',],
                'female_signifiers' => ['Donne', 'Femminile', 'Femmine']
            ],
            'parse_yob' => 1
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    public function hasValidResult($line)
    {
        $line = str_replace(' ' , '', $line);
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        // $resultIsFromRoundUp = preg_match("/[0-9] [0-9] [0-9]{1,2}./", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $name = array();
        preg_match_all('/([A-Z]{2,}\s?)+/', $line, $name);
        $lastName = trim($name[0][1]);
        preg_match('/([A-Z\x{00C0}-\x{00D0}]{1}[a-z]+\s?)+/', $line, $name);
        $firstName = trim($name[0]);

        return $firstName . " " . $lastName;

    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{2}\s/', $line, $matches);
        $yearOfBirth = $matches[0];
        return $yearOfBirth;
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $line = str_replace(' ' , '', $line);
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

    public function shouldIncludeEvent($line)
    {
        return true;
    }


}