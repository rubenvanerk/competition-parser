<?php

abstract class CompetitionParser
{
    private static $_instance;

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
     * @param $line
     * @return bool
     */
    private function hasValidResult($line)
    {
        return false;
    }

    /**
     * @param string $line
     * @return string
     */
    function getLineType($line)
    {
        return '';
    }

    /**
     * @param $line
     * @return int
     */
    public function getEventIdFromLine($line)
    {
        $disciplines = $GLOBALS['config']['parser'][strtolower(EVENT_TYPE)]['disciplines'];
        $discipline = 0;
        foreach ($disciplines as $eventId) {
            foreach ($eventId as $description) {
                if (stristr($line, $description)) {
                    $discipline = array_search($eventId, $disciplines);
                }
            }
        }
        return $discipline;
    }

    /**
     * @param $line
     * @return string
     */
    public function getGenderFromLine($line)
    {
        if ($this->lineContains($line, $GLOBALS['config']['parser']['genders']['female_signifiers'])) return 2;
        elseif ($this->lineContains($line, $GLOBALS['config']['parser']['genders']['male_signifiers'])) return 1;
        return 0;
    }

    /**
     * @param string $line
     * @return string
     */
    public function getFirstNameFromLine($line)
    {
        return '';
    }

    /**
     * @param string $line
     * @return string
     */
    function getLastNameFromLine($line)
    {
        return '';
    }

    /**
     * @param string line
     * @return string
     */
    function getYearOfBirthFromLine($line)
    {
        return '';
    }

    /**
     * @param string $line
     * @return array
     */
    function getTimesFromLine($line)
    {
        return [];
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

}