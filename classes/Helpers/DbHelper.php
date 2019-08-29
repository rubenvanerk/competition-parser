<?php namespace CompetitionParser\Classes\Helpers;

use CompetitionParser\Classes\Models\Athlete;
use CompetitionParser\Classes\Models\Competition;
use CompetitionParser\Classes\Models\IndividualResult;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use PDO;


class DbHelper
{
    private $connection;
    private $config;

    public function __construct()
    {
        $this->config = include('config.php');
        $dbConfig = $this->config['database'];
        $this->connection = new PDO(
            'pgsql:host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . ';dbname=' . $dbConfig['name'] . ';options=\'--client_encoding=UTF8\'',
            $dbConfig['username'],
            $dbConfig['password']
        );
    }

    public static function bootEloquent()
    {

        $config = include('config.php');
        $dbConfig = $config['database'];

        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'pgsql',
            'host' => $dbConfig['host'],
            'database' => $dbConfig['name'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * @param Competition $competition
     * @throws Exception
     */
    public function saveCompetitionToDatabase($competition)
    {
        $competitionId = $this->saveCompetition($competition->name);
        $competition->setCompetitionId($competitionId);
        $i = 1;
        foreach ($competition->getEvents() as $event) {
            print_r("EVENTID " . $event->getId() . PHP_EOL);
            foreach ($event->getResults() as $result) {
                print_r($i . '/' . $competition->countResults() . ' | ' . $result->getClassification() . ' round:' . $result->getRound() . ' ' . $result->getName() . ' ' . json_encode($result->getTimes()) . PHP_EOL);
                $this->saveResult($result, $event, $competition);
                $i++;
            }
        }
    }

    /**
     * @param IndividualResult $result
     * @param Event $event
     * @param Competition $competition
     * @throws Exception
     */
    private function saveResult($result, $event, $competition)
    {
        $athleteId = $this->getOrInsertAthlete($result->getName(), $result->getYearOfBirthOrNull(), $event->getGender());
        $athletesPerRound = 8;

        $round = $result->getRound();
        foreach ($result->getTimes() as $time) {
            $roundNumber = is_null($event->getRoundNumber()) ? $round : $event->getRoundNumber();
            if ($roundNumber > 0 && $result->getClassification() <= $athletesPerRound) $roundNumber++;
            $time = toSqlInterval($time);
            $stmt = $this->createInsertResultStatement($time, $athleteId, $competition->getId(), $event->getId(), $result->getOriginalLine(), $roundNumber, $result->isDq(), $result->isDns());
            $stmt->execute();
            if (intval($stmt->errorCode())) {
                print_r('Error (' . $stmt->errorCode() . ') while inserting result' . PHP_EOL);
                print_r($stmt->queryString);
                print_r($result);
                exit;
            }
            $round++;
        }
    }

    /**
     * @param $time
     * @param $athleteId
     * @param $competitionId
     * @param $eventId
     * @param $originalLine
     * @param $roundNumber
     * @param $isDq
     * @param $isDns
     * @return bool|PDOStatement
     */
    private function createInsertResultStatement($time, $athleteId, $competitionId, $eventId, $originalLine, $roundNumber, $isDq, $isDns)
    {
        return $this->connection->prepare("INSERT INTO rankings_individualresult 
              VALUES (DEFAULT, '{$time}', '{$athleteId}', '{$competitionId}', '{$eventId}', NULL, 0, '{$originalLine}', {$roundNumber}, {$isDq}, {$isDns})");

    }

    /**
     * @param $name
     * @param $yearOfBirth
     * @param $gender
     * @return int athlete id
     * @throws Exception
     */
    private function getOrInsertAthlete($name, $yearOfBirth, $gender)
    {
        $sql = "SELECT * FROM rankings_athlete WHERE LOWER(name) = LOWER('{$name}')";
        $sql .= " AND gender = {$gender}";
        if ($yearOfBirth && $yearOfBirth !== 'unknown') $sql .= " AND year_of_birth = {$yearOfBirth}";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch();
        $athleteId = $row[0];
        if (empty($athleteId)) {
            $athleteId = $this->insertAthlete($name, $yearOfBirth, $gender);
        }

        return $athleteId;
    }

    /**
     * @param $name
     * @param $yearOfBirth
     * @param $gender
     * @return mixed
     * @throws Exception
     */
    private function insertAthlete($name, $yearOfBirth, $gender)
    {
        $slug = slugify($name);
        if (!$slug) {
            throw new Exception("Created slug is empty for " . $name);
        }

        $inserted = false;

        while (!$inserted) {
            $stmt = $this->connection->prepare("SELECT * FROM rankings_athlete WHERE slug = '$slug'");
            $stmt->execute();
            if (!$stmt->fetch()) {
                $stmtRes = $this->connection->prepare("INSERT INTO rankings_athlete
                        VALUES (DEFAULT, NULL, NULL,
                        {$yearOfBirth}, '{$gender}', '{$slug}', '{$name}')")->execute();
                if (!$stmtRes)
                    throw new Exception("Failed to insert: " . print_r($name, 1));
                $inserted = true;
            } else {
                $lastChar = substr($slug, -1);
                if (is_numeric($lastChar)) {
                    $lastChar = intval($lastChar) + 1;
                    $slug = preg_replace('/-[0-9]+$/', '', $slug);
                } else {
                    $lastChar = 1;
                }
                $slug .= "-" . $lastChar;
            }
        }

        return $this->connection->lastInsertId();
    }

    /**
     * @param $competitionName
     * @return string
     */
    private function saveCompetition($competitionName)
    {
        $sql = "SELECT * FROM rankings_competition WHERE name = '{$competitionName}'";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch();
        $competitionId = $row[0];


        if (!$competitionId) {
            print_r('Add the competition before importing');
            exit;
        }

        print_r('Found competition with same name: ' . $competitionId . PHP_EOL);

        return $competitionId;
    }

    /**
     * @param \leonverschuren\Lenex\Model\Lenex $individualResult
     * @param $events
     * @param $competition
     */
    public function saveLenexCompetition(\leonverschuren\Lenex\Model\Lenex $individualResult, $events, $competition)
    {
        $resultCount = 1;

        foreach ($individualResult->getMeets() as $meet) {
            foreach ($meet->getClubs() as $club) {
                foreach ($club->getAthletes() as $athlete) {
                    if(!$athlete->getResults()) continue;
                    $athleteName = $athlete->getFirstName() . ' ' . $athlete->getLastName();
                    $gender = $athlete->getGender() == 'F' ? 2 : 1;
                    if ($athlete->getBirthDate()) {
                        $yearOfBirth = $athlete->getBirthDate()->format('Y');
                    } else {
                        $yearOfBirth = null;
                    }
                    $athleteModel = Athlete::findOrCreate(
                        $athleteName,
                        $gender,
                        $yearOfBirth,
                        $athlete->getNation()

                    );
                    foreach ($athlete->getResults() as $lenexResult) {

                        if ($lenexResult->getStatus()) {
                            continue;
                        }

                        $individualResult = new IndividualResult();
                        $individualResult->time = $lenexResult->getSwimTime();
                        $individualResult->athlete_id = $athleteModel->id;
                        $individualResult->competition_id = $competition->id;
                        $individualResult->event_id = $events[$lenexResult->getEventId()];
                        $individualResult->original_line = '';
                        $individualResult->round = 0;
                        $individualResult->disqualified = $lenexResult->getStatus() == 'DSQ' ? 'true' : 'false';
                        $individualResult->did_not_start = $lenexResult->getStatus() == 'DNS' ? 'true' : 'false';
                        $individualResult->points = 0;

                        $individualResult->save();

                        print_r($resultCount++ . ' Inserted result for ' . $athleteName . PHP_EOL);
                    }
                }
            }
        }

    }

}