<?php
include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = include('config.php');

define("EVENT_TYPE", $config['competition']['type']);

$competition = new Competition();


$competitionParser = CompetitionParser::getInstance();
$fileName = $config['pdf_folder'] . $config['competition']['filename'];

switch ($config['competition']['filetype']) {
    case 'csv':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('ENCODING', "UTF-8");
        break;
    case 'pdf':
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fileName);
        $lines = explode("\n", $pdf->getText());
        define('ENCODING', "ASCII");
        break;
    default:
        print_r('SET FILETYPE');
        break;
}

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
            $name = $competitionParser->getNameFromLine($line);
            $yearOfBirth = $competitionParser->getYearOfBirthFromLine($line);
            $times = $competitionParser->getTimesFromLine($line);
            $result = Result::create($name, $yearOfBirth, $times);

            $competition->addResultToCurrentEvent($result);
            break;
    }
}
try {
//    printCompetition($competition);
    $dbHelper = new DbHelper();
    $dbHelper->saveCompetitionToDatabase($competition);
} catch (Exception $e) {
    print_r('Something terrible happened' . PHP_EOL);
    print_r($e->getMessage());
}