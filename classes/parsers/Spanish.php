<?php

class Spanish extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['masculino', 'femenino', 'm.'],
            'event_designifiers' => ['Elim.T'], // signifies a line is definitely not an event line
            'result_rejectors' => ['00:00:00'],
            'disciplines' => [
                1 => ["100 m. remolque de maniquí"],
                2 => ["50 m. remolque de maniquí"],
                3 => ["200 m. natación con obstáculos"],
                4 => ["100 m. socorrista"],
                5 => ["100 m. combinada de salvamento"],
                6 => ["200 m. supersocorrista", "200 m. súper socorrista"],
                7 => ["50 m obstacle swim"],
                8 => ["50 m free style"],
                9 => ["50 m freestyle with fins"],
                10 => ["50 m manikin"],
                11 => ["50 m slepen"],
                12 => ["25 m pop"],
                13 => ["50 m vrij met torpedo"],
                14 => ["50 m pop met vliezen"],
            ],
            'genders' => [
                'male_signifiers' => ['masculino', 'M'],
                'female_signifiers' => ['femenino', 'F']
            ],
            'parse_yob' => 1
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }

    protected function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}:[0-9]{2}:[0-9]{2}/", $line);
        $hasComma = preg_match("/,/", $line);
        preg_match_all("/[0-9]{2}:[0-9]{2}:[0-9]{2}/", $line, $times);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        if(count($times[0]) > 2) print_r($line . PHP_EOL);
        return $hasComma && $hasResult && count($times[0]) > 0 && count($times[0]) < 3 && $isValid;
    }

    /**
     * @param string $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $name = array();
        preg_match('/([\x{0040}-\x{00ff}\s-\.]+,\s[\x{0040}-\x{00ff}\s-\.]+)/', utf8_decode($line), $name);
        if($name) {
            $name = trim($name[0]);

            $pieces = explode(', ', $name);
            $firstName = end($pieces);
            array_pop($pieces);
            array_unshift($pieces, $firstName);
            $pieces = array_map('trim', $pieces);

            $name = implode(' ', $pieces);

            return preg_replace('!\s+!', ' ', trim($name));
        } else {
            preg_match('/[0-9]{4}\s+[\x{0040}-\x{00ff}\s-\.]+/', utf8_decode($line), $name);
            $name = trim($name[0]);
            $name = preg_replace('/[0-9]{4}/', '', $name);
            return preg_replace('!\s+!', ' ', trim($name));
        }
    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{4}\s/', $line, $matches);
        if(!isset($matches[0])) print_r($line . PHP_EOL);
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
        preg_match_all('/[0-9]{2}:[0-9]{2}:[0-9]{2}/', $line, $times);
        $i = 0;
        foreach ($times[0] as $time) {
            $time[5] = ".";
            $times[$i] = $time;
            $i++;
        }
        if(count($times) > 2) {
            return [];
        }
        return $times;
    }

    public function createUsableLines($lines, $type)
    {
        if(!$type) return $lines;

        $usableLines = [];
        $newLine = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if(preg_match('/m./', $line)) {
                $usableLines[] = $newLine;
                $usableLines[] = $line;
                $newLine = '';
            } elseif($line !== '') {
                $newLine .= ' ' . $line;
            } else {
                $usableLines[] = $newLine;
                $newLine = '';
            }
        }
        return $usableLines;
    }

    protected function shouldIncludeEvent($line)
    {
        return true;
    }
}