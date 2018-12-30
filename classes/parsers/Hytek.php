<?php

class Hytek extends CompetitionParser
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

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['hytek']['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $GLOBALS['config']['parser']['hytek']['genders']['male_signifiers'])) return 1;
        return 0;
    }

    public function getLineType($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['hytek']['event_signifiers'])
            && !$this->lineContains($line, $GLOBALS['config']['parser']['hytek']['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) return 'result';
        return '';
    }

    private function hasValidResult($line)
    {
        $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
        $isValid = !$this->lineContains($line, $GLOBALS['config']['parser']['hytek']['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param $line
     * @return string
     */
    function getNameFromLine($line)
    {
        $matches = array();
        preg_match("/[A-z-\s]+,\s[A-z-\s]+/", $line, $matches);
        $name = $matches[0];
        $nameParts = explode(',', $name);
        $nameParts = array_map('trim', $nameParts);
        $nameParts = array_reverse($nameParts);

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
        return [end($times[0])];
    }

    function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $GLOBALS['config']['parser']['hytek']['event_rejectors']);
    }
}