<?php

abstract class CompetitionParser
{
    /** @var CompetitionParser $_instance */
    private static $_instance;
    protected $config = [];

    /**
     * @return CompetitionParser
     */
    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            /** @var CompetitionParser $competitionParser */
            $competitionParser = EVENT_TYPE;

            if (is_subclass_of($competitionParser, __CLASS__)) {
                self::$_instance = $competitionParser::getInstance();
            }
        }
        return self::$_instance;
    }

    /**
     * @param \Smalot\PdfParser\Document $pdf
     * @return array
     */
    public function getLines(\Smalot\PdfParser\Document $pdf)
    {
        return explode("\n", $pdf->getText());
    }


    /**
     * @param $line
     * @return bool
     */
    abstract protected function hasValidResult($line);

    /**
     * @param string $line
     * @return string
     */
    public function getLineType($line)
    {
        if ($this->lineContains($line, self::$_instance->config['event_signifiers'])
            && !$this->lineContains($line, self::$_instance->config['event_designifiers'])) {
            return 'event';
        } elseif (self::$_instance->hasValidResult($line)) {
            return 'result';
        } elseif (self::$_instance->getGenderFromLine($line)) {
            return 'gender';
        }

        print_r($this->lineContains($line, self::$_instance->config['event_signifiers']));

        return '';
    }

    /**
     * @param $line
     * @return int
     */
    public function getEventIdFromLine($line)
    {
        $disciplines = self::$_instance->config['disciplines'];

        $discipline = 0;
        foreach ($disciplines as $eventId) {
            foreach ($eventId as $description) {
                if (stristr($line, $description)) {
                    $discipline = array_search($eventId, $disciplines);
                }
            }
        }

        if (!$discipline) {
            print_r($line . " " . $discipline . PHP_EOL);
        }

        return $discipline;
    }

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, self::$_instance->config['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, self::$_instance->config['genders']['male_signifiers'])) return 1;
        return 0;
    }

    /**
     * @param $line
     * @return string
     */
    public abstract function getNameFromLine($line);

    /**
     * @param string line
     * @return string
     */
    public abstract function getYearOfBirthFromLine($line);

    /**
     * @param string $line
     * @return array
     */
    public abstract function getTimesFromLine($line);

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
    function createUsableLines($lines, $type) {
        return $lines;
    }

    /**
     * @param $line
     * @return bool
     */
    public abstract function shouldIncludeEvent($line);
}