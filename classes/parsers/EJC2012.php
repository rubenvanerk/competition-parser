<?php

class EJC2012 extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

        self::$_instance->config = [
            'event_signifiers' => ['ATTEMPT', 'FINAL', 'Women', 'Men'],
            'event_designifiers' => ['Clement Marco'], // signifies a line is definitely not an event line
            'event_rejectors' => ['4x50', '4x25', 'ATTEMPT 5'], // rejects current event, results below this are not included
            'result_rejectors' => ['DSQ', 'Girls', 'Boys', '4x50m', 'Skapad av WinGrodan'],
            'parse_yob' => 1,
            'disciplines' => [
                1 => ["100m mAnikin cArry w fins,", "100m Manikin Carry"],
                2 => ["50m mAnikin cArry"],
                3 => ["200m Obstacle Swim"],
                4 => ["100m mAnikin wow w fins", " 100m mAnikin tow w fins", "100m Manikin Tow w. Fins"],
                5 => ["100m Rescue Medley"],
                6 => ["200m Super Lifesaver"],
                7 => ["50m Obstacle Swim"],
                9 => ["50m Freestyle with Fins"],
                10 => ["50m Manikin Carry (relay leg 3)"],
                12 => ["25m Manikin Carry"],
                14 => ["50m Manikin Carry with Fins (relay leg 4)"],
            ],
            'genders' => [
                'male_signifiers' => ['MEN'],
                'female_signifiers' => ['WOMEN']
            ]
        ];

        define("PARSE_YOB", self::$_instance->config['parse_yob']);

        return self::$_instance;
    }


    public function createUsableLines($lines, $type) 
    {
        $i = 0;
        foreach($lines as $line) 
        {

            if(preg_match("/^\s*[0-9]{0,2}[:]?[0-9]{1,2}[.][0-9]{2}\s*$/", $line)) {
                $lines[$i - 1] .= ' ' . $line;
                $lines[$i] = '';
                $line = $lines[$i];
            }


            if(!preg_match("/[0-9]{4}/", $line) && $this->getLineType($line) == 'result') {
                $lines[$i] = '';
            }


            $i++;
        }
        return $lines;
    }

    private function getFirstNumberOffset($string){
        preg_match('/^\D*(?=\d)/', $string, $m);
        return isset($m[0]) ? strlen($m[0]) : FALSE;
    }

    public function hasValidResult($line)
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
        $matches = [];
        preg_match('/([A-z\x{0060}-\x{00ff}\,?\-]+?\s?)+/', $line, $matches);
        return $matches[0];
    }


    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/[0-9]{4}/', $line, $matches);
        return substr(trim($matches[0]), 2, 2);
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