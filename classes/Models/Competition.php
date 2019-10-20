<?php namespace CompetitionParser\Classes\Models;

use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
    protected $table = 'rankings_competition';
    public $timestamps = false;

    private $competitionId;
    private $events;

    public static function findOrCreate($name, $date, $location, $fileName)
    {
        $competition = Competition::where('name', $name)->first();

        if (!$competition) {
            $competition = new Competition();
            $competition->name = $name;
            $competition->slug = slugify($name);
            $competition->date = $date;
            $competition->location = $location;
            $competition->file_name = $fileName;

            $competition->type_of_timekeeping = 0; // unknown
            $competition->is_concept = false;
            $competition->status = 1; // scheduled for import

            $competition->save();
        }

        return $competition;
    }


    /**
     * @param Event $event
     * @return bool
     */
    public function addEvent($event)
    {
        if (!is_null($event) && get_class($event) == 'CompetitionParser\Classes\Models\Event'
            && $this->currentEventIsNullOrDifferent($event)) {
            $this->events[] = $event;
            return true;
        } elseif (is_null($event)) {
            $this->events[] = $event;
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param IndividualResult $result
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
    public function getCurrentEvent()
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
            || $event->getId() !== $currentEvent->getId()
            || $event->getRoundNumber() !== $currentEvent->getRoundNumber()) return true;
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