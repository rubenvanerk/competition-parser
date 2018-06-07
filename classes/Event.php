<?php

class Event
{
    private $eventId;
    private $gender;
    private $results;

    /**
     * Event constructor.
     * @param int $eventId
     * @param int $gender 0 = unknown, 1 = male, 2 = female
     */
    public function __construct($eventId, $gender)
    {
        $this->eventId = $eventId;
        $this->gender = $gender;
        $this->results = [];
    }

    public static function create($eventId, $gender)
    {
        if($eventId && $gender) {
            return new Event($eventId, $gender);
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

    public function getGenderName()
    {
        if ($this->gender == 1) return "Men";
        elseif ($this->gender == 2) return "Women";
        else return "GENDER UNKNOWN";
    }
}