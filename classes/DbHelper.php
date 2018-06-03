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
            'pgsql:host=' . $dbConfig['host'] . ';port=' . $dbConfig['port'] . ';dbname=' . $dbConfig['name'],
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
            foreach ($event->getResults() as $result) {
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
        $time = toSqlInterval($result->getFirstTime());
        $athleteId = $this->getOrInsertAthlete($result, $event);
        $stmt = $this->connection->prepare("INSERT INTO rankings_individualresult 
              VALUES (DEFAULT, '{$time}', '{$athleteId}', '{$competition->getId()}', '{$event->getId()}')");
        $stmt->execute();
    }

    /**
     * @param Result $result
     * @param Event $event
     * @return int athlete id
     */
    private function getOrInsertAthlete($result, $event) {
        $stmt = $this->connection->prepare("SELECT * FROM rankings_athlete WHERE LOWER(last_name) = LOWER('{$result->getEscapedLastName()}')
                                AND LOWER(first_name) = LOWER('{$result->getFirstName()}') AND year_of_birth = {$result->getYearOfBirth()}");
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
     */
    private function insertAthlete($result, $event) {
        $slug = slugify($result->getFirstName() . " " . $result->getLastName());

        $inserted = false;

        while(!$inserted) {
            $stmt = $this->connection->prepare("SELECT first_name, last_name FROM rankings_athlete WHERE slug = '$slug'");
            $stmt->execute();
            if (!$stmt->fetch()) {
                $this->connection->prepare("INSERT INTO rankings_athlete
                        VALUES (DEFAULT, '{$result->getFirstName()}', '{$result->getEscapedLastName()}',
                        '{$result->getYearOfBirth()}', '{$event->getGender()}', '{$slug}')")->execute();
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

        $stmt = $this->connection->prepare("SELECT * FROM rankings_athlete WHERE LOWER(last_name) = LOWER('{$result->getEscapedLastName()}')
                                AND LOWER(first_name) = LOWER('{$result->getFirstName()}') AND year_of_birth = {$result->getYearOfBirth()}");
        $stmt->execute();
        $row = $stmt->fetch();
        $athleteId = $row[0];

        return $athleteId;
    }

    private function saveCompetition()
    {
        $stmt = $this->connection->prepare("INSERT INTO rankings_competition VALUES 
        (DEFAULT, 
        '{$this->config['competition']['name']}', 
        '{$this->config['competition']['date']}', 
        '{$this->config['competition']['location']}', 
        '{$this->config['competition']['clocktype']}', 
        '" . slugify($this->config['competition']['name']) . "'
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