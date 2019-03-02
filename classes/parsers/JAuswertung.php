<?php

class JAuswertung extends CompetitionParser
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

    /**
    * @param array $lines
    * @param int $type
    * @return array
    */
    function createUsableLines($lines, $type)
    {
        $resultLines = $lines;
        switch ($type) {
            case 'event':
                $i = 0;
                foreach ($lines as $line) {
                    if($this->lineContains($line, ['Ergebnisse']))
                    {
                        $resultLines[$i] .= ' ' . $lines[$i + 2];
                    }
                    $resultLines[$i] = $regular_spaces = preg_replace('/\xc2\xa0/', ' ', $resultLines[$i]);
                    $i++;
                }
                break;
        }
        return $resultLines;
    }

    public function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]:[0-9]{2}\,[0-9]{2}/", $line);
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
        preg_match("/(\s[A-z\x{0060}-\x{00ff}]+-?)+,\s([A-z\x{0060}-\x{00ff}]+-?)+/", utf8_decode($line), $matches);

        $name = $matches[0];
        $nameParts = explode(',', $name);
        $nameParts = array_map('trim', $nameParts);
        $nameParts = array_reverse($nameParts);

        $name = implode(' ', $nameParts);
        return trim($name);
    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        $matches = array();
        preg_match('/\s[0-9]{2}\b/', $line, $matches);
        return trim($matches[0]);
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        $times = array();
        preg_match_all('/[0-9]{1}:?[0-9]{2}\,[0-9]{2}/', $line, $times);
        return str_replace(',', '.', $times[0]);
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }

    /**
     * @param \Smalot\PdfParser\Document $pdf
     * @return array
     * @throws Exception
     */
    public function getLines(\Smalot\PdfParser\Document $pdf)
    {
        $lines = [];
        foreach ($pdf->getPages() as $page) {
            $pageLines = explode("\n", $page->getText());
            $lines = array_merge($lines, array_reverse($pageLines));
        }
        return $lines;
    }

}