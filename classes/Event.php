<?php

class Event
{
    private $eventId;
    private $gender;
    private $results;
    private $originalLine;

    /**
     * Event constructor.
     * @param int $eventId
     * @param int $gender 0 = unknown, 1 = male, 2 = female
     */
    public function __construct($eventId, $gender, $line)
    {
        $this->eventId = $eventId;
        $this->gender = $gender;
        $this->originalLine = $line;
        $this->results = [];
    }

    /**
     * @param $eventId
     * @param $gender
     * @param $includeEvent
     * @param $line
     * @return Event|null
     */
    public static function create($eventId, $gender, $includeEvent, $line)
    {
        if($eventId && $gender && $includeEvent) {
            $event = new Event($eventId, $gender, $line);
            return $event;
        }
        return null;
    }

    /**
     * @param Result $result
     */
    public function addResult($result)
    {
        $this->results[] = $result;
    }

    /**
     * @return Result[]
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
}