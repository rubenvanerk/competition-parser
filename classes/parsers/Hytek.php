<?php

class Hytek extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['Event'],
            'event_designifiers' => ['Events'], // signifies a line is definitely not an event line
            'event_rejectors' => ['Under 14', 'Relay'], // rejects current event, results below this are not included
            'result_rejectors' => ['SA REC', 'National:', 'APLSC:', 'WORLD:', 'Euro:', 'World: ', 'Australian:', '10:00.00', '5:00.00', 'DQ'],
            'parse_yob' => 0,
            'disciplines' => [
                1 => ["100 LC Metre Fins Manikin Carry", "100 LC Meter Manikin Rescue", "100 LC Meter Manikin Carr", "100 LC Meter manikin carry", "100 LC Meter Manikin Carry", "Manikin Carry w/Fins"],
                2 => ["50 LC Metre Manikin Carry", "50 LC Meter Manikin Rescue", "50 LC Meter Manikin Carr", "50 LC Meter mankin carry"],
                3 => ["200 LC Metre Obstacle", "200 LC Metre Masters Obstacle", "200 LC Meter Obstacle", "200 LC Meter Obstacles", "200 LC Meter 0bstacles"],
                4 => ["100 LC Metre Fins Manikin Tow", "100 LC Meter Manikin Tow", "100 LC Meter Manikin Tow", "100 LC Meter Manikin Tow"],
                5 => ["100 LC Metre Rescue Medley", "100 LC Meter Rescue Medley", "100 LC Meter Rescue Medle", "Rescue Medley"],
                6 => ["200 LC Metre Super Lifesaver", "200 LC Meter Super Lifesaver", "200 LC Meter Su", "uper Lifesaver"],
                7 => ["50m Nuoto con ostac45oli"],
                8 => ["50 m freeffff style"],
                10 => ["50 m mafffnikin"],
                11 => ["50 m slefffpen"],
                12 => ["25 m pfffop"],
                13 => ["50 m vrifffj met torpedo"],
                14 => ["50m Manichino455 pinne"],
            ],
            'genders' => [
                'male_signifiers' => ['Men', 'Boys'],
                'female_signifiers' => ['Women', 'Girls']
            ]
        ];
        
        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    /**
     * @param array $lines
     * @param int $type
     * @return array
     */
    function createUsableLines($lines, $type)
    {
        $resultLines = $lines;
        switch ($type) {
            case 'sa-champs':
                $i = 0;
                foreach ($lines as $line) {
                    if((preg_match('/\s*[0-9]{0,2}:?[0-9]{2}\.[0-9]{2}/', $line)) && !strpos($line, 'SA REC 2017'))
                    {
                        $resultLines[$i - 1] .= " " . $line;
                        $resultLines[$i] = '';
                    }
                    $i++;
                }
                break;
            case 'irish-2018':
                $i = 0;
                foreach ($lines as $line) {
                    if((preg_match('/[0-9]{0,2}:?[0-9]{2}\.[0-9]{2}/', $line)))
                    {
                        $resultLines[$i] .= " " . $lines[$i + 1];
                    }
                    $i++;
                }
                break;
            case 'aus-2018':
                $i = 0;
                foreach ($lines as $line) {

                    if($this->getLineType($line) == 'event' && strlen($line) < 20) {
                        $resultLines[$i] .= ' ' . $lines[$i + 1];
                    }

                    $i++;
                }
                break;
            case 'something-else':
                $i = 0;
                foreach ($lines as $line) {
                    if(($position = strpos($line, 'Event')) && $position > 10)
                    {
                        $begin = substr($line, 0, $position);
                        $end = substr($line, $position);
                        $resultLines[$i] = $begin;
                        array_splice($resultLines, $i + 1, 0, $end);
                        // increase $i since we just inserted a line that's not in $lines but is in $resultLines
                        $i++;
                    }
                    $i++;
                }
                break;
        }
        return $resultLines;
    }

    protected function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $matches = array();
        preg_match("/[A-z-\s\*]+,\s[A-z-\s\*]+/", $line, $matches);
        $name = $matches[0];
        $nameParts = explode(',', $name);
        $nameParts = array_map('trim', $nameParts);
        $nameParts = array_reverse($nameParts);


        $nameParts[0] = preg_replace('/\*/', '', $nameParts[0]);
        $nameParts[1] = preg_replace('/\*/', '', $nameParts[1]);

        $nameParts[0] = preg_replace('/\s*-/', '-', $nameParts[0]);
        $nameParts[1] = preg_replace('/\s*-/', '-', $nameParts[1]);

        $nameParts[0] = preg_replace('/\s*(?=[a-z])/', '', $nameParts[0]);
        $nameParts[1] = preg_replace('/\s*(?=[a-z])/', '', $nameParts[1]);

        $name = implode(' ', $nameParts);
        return trim($name);
    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        return 'unknown';
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match_all('/([0-9]:)?[0-9]{2}\.[0-9]{2}/', $line, $times);
        return [current($times[0])];
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }
}