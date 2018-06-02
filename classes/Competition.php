<?php

class Competition
{
    private $competitionId;
    private $events;

    public function __construct()
    {
        $this->events = [];
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function addEvent($event)
    {
        if (get_class($event) == 'Event') {
            $this->events[] = $event;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Result $result
     */
    public function addResultToCurrentEvent($result)
    {
        /** @var Event $currentEvent */
        if ($currentEvent = array_values(array_slice($this->events, -1))[0]) {
            $currentEvent->addResult($result);
        }
    }

    /**
     * @param $competitionId
     */
    public function setCompetitionId($competitionId)
    {
        $this->competitionId = $competitionId;
    }

    /**
     * @return Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->competitionId;
    }


}