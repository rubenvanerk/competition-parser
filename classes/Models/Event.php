<?php namespace CompetitionParser\Classes\Models;

class Event
{
    private $eventId;
    private $gender;
    private $results;
    private $originalLine;
    private $roundNumber = null;

    /**
     * Event constructor.
     * @param int $eventId
     * @param int $gender 0 = unknown, 1 = male, 2 = female
     * @param $line
     * @param int $roundNumber
     */
    public function __construct($eventId, $gender, $line, $roundNumber = null)
    {
        $this->eventId = $eventId;
        $this->gender = $gender;
        $this->originalLine = $line;
        $this->roundNumber = $roundNumber;
        $this->results = [];
    }

    /**
     * @param $eventId
     * @param $gender
     * @param $includeEvent
     * @param $line
     * @param int $roundNumber
     * @return Event|null
     */
    public static function create($eventId, $gender, $includeEvent, $line, $roundNumber = null)
    {
        if($eventId && ($gender || SEPARATE_GENDER) && $includeEvent) {
            $event = new Event($eventId, $gender, $line, $roundNumber);
            return $event;
        }
        if($includeEvent) {
            print_r('EVENT COULD NOT BE PARSED, VALUES(event, gender, includeevent): ' . $eventId . ' ' . $gender . ' ' . $includeEvent . PHP_EOL);
            print_r($line . PHP_EOL . PHP_EOL);
        }
        return null;
    }

    /**
     * @param IndividualResult $result
     */
    public function addResult($result)
    {
        $this->results[] = $result;
    }

    /**
     * @return IndividualResult[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->eventId;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return string
     */
    public function getOriginalLine()
    {
        return $this->originalLine;
    }

    public function getGenderName()
    {
        if ($this->gender == 1) return "Men";
        elseif ($this->gender == 2) return "Women";
        else return "GENDER UNKNOWN";
    }

    public function setRoundNumber($roundNumber)
    {
        $this->roundNumber = $roundNumber;
    }

    public function getRoundNumber()
    {
        return $this->roundNumber;
    }

    public function countResults()
    {
        return count($this->results);
    }
}