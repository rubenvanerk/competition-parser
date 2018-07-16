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
        $competitionId = $this->saveCompetition();
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
              VALUES (DEFAULT, '{$time}', '{$athleteId}', '{$competition->getId()}', '{$event->getId()}')");
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
                    throw new Exception("Failed to insert: " . $result->getName());
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

    private function saveCompetition()
    {
        $stmt = $this->connection->prepare("INSERT INTO rankings_competition VALUES 
        (DEFAULT, 
        '{$this->config['competition']['name']}', 
        '{$this->config['competition']['date']}', 
        '{$this->config['competition']['location']}', 
        '{$this->config['competition']['clocktype']}', 
        '" . slugify($this->config['competition']['name']) . "', 
        'true'
        )");
        $stmt->execute();

        $getCompId = $this->connection->prepare(
            "SELECT id FROM rankings_competition 
                      WHERE slug = '" . slugify($this->config['competition']['name']) . "'"
        );
        $getCompId->execute();
        $competitionRow = $getCompId->fetch();
        return $competitionRow[0];
    }
}