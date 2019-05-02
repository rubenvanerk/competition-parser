<?php

class CompetitionParser
{
    protected $config;
    private $nameRegex;
    private $timeRegex;
    private $yobRegex;
    private $timeIndex;

    public function __construct($config)
    {
        $this->config = yaml_parse_file('config.yaml');
        $this->config = array_merge($this->config, $config);
        define("PARSE_YOB", intval($this->config['parser_config']['formats']['yob_format']) > 0);

        $this->nameRegex = $this->config['regex']['name'][$this->config['parser_config']['formats']['name_format']];
        $this->timeIndex = $this->config['parser_config']['time_index'];
        $this->timeRegex = $this->config['regex']['time'][$this->config['parser_config']['formats']['time_format']];
        if (PARSE_YOB) {
            $this->yobRegex = $this->config['regex']['yob'][$this->config['parser_config']['formats']['yob_format']];
        }

    }

    /**
     * @param string $line
     * @return string
     */
    public function getLineType($line)
    {
        if ($this->lineContains($line, $this->config['event_signifiers'])
            && !$this->lineContains($line, $this->config['event_designifiers'])) {
            return 'event';
        } elseif ($this->hasValidResult($line)) {
            return 'result';
        } elseif ($this->getGenderFromLine($line)) {
            return 'gender';
        }

        return '';
    }

    /**
     * @param $line
     * @return int
     */
    public function getEventIdFromLine($line)
    {
        $disciplines = $this->config['disciplines'];

        $discipline = 0;
        foreach ($disciplines as $eventId) {
            foreach ($eventId as $description) {
                if (stristr($line, $description)) {
                    $discipline = array_search($eventId, $disciplines);
                }
            }
        }

        if (!$discipline) {
            print_r('could not find event in: ' . $line . PHP_EOL);
        }

        return $discipline;
    }

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $this->config['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $this->config['genders']['male_signifiers'])) return 1;
        return 0;
    }

    /**
     * checks if any of the values in array occurs in string
     * @param string $line
     * @param array $needles
     * @return bool
     */
    function lineContains($line, array $needles)
    {
        foreach ($needles as $needle) {
            if (stripos($line, $needle) !== false) return true;
        }
        return false;
    }

    /**
     * @param array $lines
     * @param int $type
     * @return array
     */
    function createUsableLines($lines)
    {
        return $lines;
    }

    /**
     * @param $line
     * @return bool
     */
    protected function hasValidResult($line)
    {
        $hasResult = preg_match($this->timeRegex, $line);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        return $hasResult && $isValid;
    }

    /**
     * @param $line
     * @return string
     */
    public function getNameFromLine($line)
    {
        $matches = array();
        preg_match($this->nameRegex, $line, $matches);

        $name = $matches[0];

        if (strpos($name, ',') !== false) {
            $nameParts = explode(',', $name);
            $nameParts = array_map('trim', $nameParts);
            $nameParts = array_reverse($nameParts);
            $name = implode(' ', $nameParts);
        }

        $name = preg_replace("/\-{2,}/", "", $name); // remove multiple -
        $name = preg_replace("/\s{2,}/", " ", $name); // replace multiple space with one
        $name = preg_replace("/W\s(?=[a-z])/", "W", $name); // replace W\s with W

        return trim($name);
    }

    /**
     * @param string line
     * @return string
     */
    public function getYearOfBirthFromLine($line)
    {
        if(!PARSE_YOB) return 'unknown';
        $matches = array();
        preg_match($this->yobRegex, $line, $matches);
        if (!$matches) print_r($line . PHP_EOL);

        $yearOfBirth = intval(trim($matches[0]));
        $yearOfBirth += ($yearOfBirth < 19 ? 2000 : 1900);
        return $yearOfBirth;
    }

    /**
     * @param string $line
     * @return array
     */
    public function getTimesFromLine($line)
    {
        $times = array();
        preg_match_all($this->timeRegex, $line, $times);
        $times = $times[0];
        switch ($this->timeIndex) {
            case 'all':
                return $times;
                break;
            case 'first':
                return [current($times)];
                break;
            case 'last':
                return [end($times)];
                break;
            default:
                $index = intval($this->timeIndex);
                return [$times[$index]];
                break;
        }
    }

    /**
     * @param $line
     * @return bool
     */
    public function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }
}