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
        if (get_class($event) == 'Event'
            && $this->currentEventIsNullOrDifferent($event)) {
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


}