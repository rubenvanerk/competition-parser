<?php

class Competition
{
    private $competitionId;
    private $events;
    public $name;
    public $date;
    public $location;
    public $clockType;

    public function __construct($name, $date, $location, $clockType)
    {
        $this->events = [];
        $this->name = $name;
        $this->date = $date;
        $this->location = $location;
        $this->clockType = $clockType;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function addEvent($event)
    {
        if (!is_null($event) && get_class($event) == 'Event'
            && $this->currentEventIsNullOrDifferent($event)) {
            $this->events[] = $event;
            return true;
        } elseif (is_null($event)) {
            $this->events[] = $event;
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
        if ($currentEvent = $this->getCurrentEvent()) {
            $currentEvent->addResult($result);
        }
    }

    /**
     * @return Event|null
     */
    private function getCurrentEvent()
    {
        if ($this->events) {
            return array_values(array_slice($this->events, -1))[0];
        }
        return null;
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

    /**
     * @param Event $event
     * @return bool
     */
    private function currentEventIsNullOrDifferent($event)
    {
        $currentEvent = $this->getCurrentEvent();
        if (!$currentEvent) return true;
        elseif ($event->getGender() !== $currentEvent->getGender()
            || $event->getId() !== $currentEvent->getId()) return true;
        return false;
    }

    public function removeNullEvents()
    {
        $this->events = array_filter($this->events, function($var){return !is_null($var);} );
    }

    /**
     * @return bool
     */
    public function hasCurrentEvent()
    {
        return !is_null($this->getCurrentEvent());
    }

    public function countResults() {
        $count = 0;
        foreach ($this->events as $event) {
            $count += count($event->getResults());
        }
        return $count;
    }

}