<?php

class JAuswertung extends CompetitionParser
{
    private static $_instance;

    public static function getInstance()
    {
        define("PARSE_YOB", $GLOBALS['config']['parser'][strtolower(self::class)]['parse_yob']);
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self;
        }

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

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['jauswertung']['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $GLOBALS['config']['parser']['jauswertung']['genders']['male_signifiers'])) return 1;
        return 0;
    }

    public function getLineType($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['jauswertung']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['jauswertung']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]:[0-9]{2}\,[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['jauswertung']['result_rejectors']);
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
        return !$this->lineContains($line, $GLOBALS['config']['parser']['jauswertung']['event_rejectors']);
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