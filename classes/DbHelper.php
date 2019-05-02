<?php

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

    /**
     * @param Competition $competition
     * @throws Exception
     */
    public function saveCompetitionToDatabase($competition)
    {
        $competitionId = $this->saveCompetition($competition);
        $competition->setCompetitionId($competitionId);
        foreach ($competition->getEvents() as $event) {
            print_r("EVENTID " . $event->getId() . PHP_EOL);
            foreach ($event->getResults() as $result) {
                print_r($result->getName() . ' ' . json_encode($result->getTimes()) . PHP_EOL);
                $this->saveResult($result, $event, $competition);
            }
        }
    }

    /**
     * @param Result $result
     * @param Event $event
     * @param Competition $competition
     * @throws Exception
     */
    private function saveResult($result, $event, $competition)
    {
        $athleteId = $this->getOrInsertAthlete($result, $event);

        foreach($result->getTimes() as $time) {
            $time = toSqlInterval($time);
            $stmt = $this->connection->prepare("INSERT INTO rankings_individualresult 
              VALUES (DEFAULT, '{$time}', '{$athleteId}', '{$competition->getId()}', '{$event->getId()}', NULL, 0, '{$result->getOriginalLine()}')");
            $stmt->execute();
        }
    }

    /**
     * @param Result $result
     * @param Event $event
     * @return int athlete id
     * @throws Exception
     */
    private function getOrInsertAthlete($result, $event) {
        $sql = "SELECT * FROM rankings_athlete WHERE LOWER(name) = LOWER('{$result->getName()}')";
        $sql .= " AND gender = {$event->getGender()}";
        if($result->getYearOfBirth() !== 'unknown') $sql .= " AND year_of_birth = {$result->getYearOfBirth()}";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $row = $stmt->fetch();
        $athleteId = $row[0];
        if (empty($athleteId)) {
            $athleteId = $this->insertAthlete($result, $event);
        }

        return $athleteId;
    }

    /**
     * @param Result $result
     * @param Event $event
     * @return mixed
     * @throws Exception
     */
    private function insertAthlete($result, $event) {
        $slug = slugify($result->getName());
        if(!$slug) {
            throw new Exception("Created slug is empty for " . $result->getName());
        }

        $inserted = false;

        while(!$inserted) {
            $stmt = $this->connection->prepare("SELECT * FROM rankings_athlete WHERE slug = '$slug'");
            $stmt->execute();
            if (!$stmt->fetch()) {
                $stmtRes = $this->connection->prepare("INSERT INTO rankings_athlete
                        VALUES (DEFAULT, NULL, NULL,
                        {$result->getYearOfBirthOrNull()}, '{$event->getGender()}', '{$slug}', '{$result->getName()}')")->execute();
                if(!$stmtRes)
                    throw new Exception("Failed to insert: " . print_r($result, 1));
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
     * @param Competition $competition
     * @return string
     */
    private function saveCompetition($competition)
    {
        $competitionSlug = slugify($competition->name);
        if(strlen($competitionSlug) >= 50) $competitionSlug = substr($competitionSlug, 0, 49);

        $stmt = $this->connection->prepare("INSERT INTO rankings_competition VALUES 
        (DEFAULT, 
        '{$competition->name}', 
        '{$competition->date}', 
        '{$competition->location}', 
        '{$competition->clockType}', 
        '" . $competitionSlug . "', 
        'true'" .
        ", false)"
        );
        $stmt->execute();

        print_r($stmt);

        if($stmt->errorCode() == '23505') {
            $sql = "SELECT * FROM rankings_competition WHERE name = '{$competition->name}'";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();

            $row = $stmt->fetch();
            $competitionId = $row[0];

            print_r('Found competition with same name: ' . $competitionId . PHP_EOL);
            sleep(5);

            return $competitionId;
        }

        return $this->connection->lastInsertId();
    }
}