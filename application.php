<?php
include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = include('config.php');

define("EVENT_TYPE", $config['competition']['type']);

$competition = new Competition();

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($config['pdf_folder'] . $config['competition']['filename']);

$competitionParser = CompetitionParser::getInstance();

$lines = explode("\n", $pdf->getText());
foreach ($lines as $line) {
    $lineType = $competitionParser->getLineType($line);
    switch ($lineType) {
        case 'event':
            $eventId = $competitionParser->getEventIdFromLine($line);
            $gender = $competitionParser->getGenderFromLine($line);
            $event = Event::create($eventId, $gender);
            $competition->addEvent($event);
            break;
        case 'result':
            $firstName = $competitionParser->getFirstNameFromLine($line);
            $lastName = $competitionParser->getLastNameFromLine($line);
            $yearOfBirth = $competitionParser->getYearOfBirthFromLine($line);
            $times = $competitionParser->getTimesFromLine($line);
            $result = Result::create($firstName, $lastName, $yearOfBirth, $times);

            $competition->addResultToCurrentEvent($result);
            break;
    }
}
try {
    printCompetition($competition);
    $dbHelper = new DbHelper();
    $dbHelper->saveCompetitionToDatabase($competition);
} catch (Exception $e) {
    print_r('Something terrible happened' . PHP_EOL);
    print_r($e->getMessage());
}