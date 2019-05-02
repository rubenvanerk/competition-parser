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
        $lines = $competitionParser->getLines($pdf);
        define('ENCODING', "UTF-8");
        break;
    case 'text':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('ENCODING', "UTF-8");
        break;
    default:
        print_r('SET FILETYPE');
        break;
}

$lines = $competitionParser->createUsableLines($lines, $config['competition']['line_conversion']);

writeToFile($lines);

$i = 1;
foreach ($lines as $line) {
    $lineType = $competitionParser->getLineType($line);
    switch ($lineType) {
        case 'event':
            $eventId = $competitionParser->getEventIdFromLine($line);
            $gender = $competitionParser->getGenderFromLine($line);
            $includeEvent = $competitionParser->shouldIncludeEvent($line);
            $event = Event::create($eventId, $gender, $includeEvent, $line);
            $competition->addEvent($event);
            break;
        case 'gender':
            $gender = $competitionParser->getGenderFromLine($line);
            $events = $competition->getEvents();

            /** @var Event $currentEvent */
            $currentEvent = end($events);
            if(is_null($currentEvent) || !$currentEvent) continue;

            $event = Event::create($currentEvent->getId(), $gender, true, $currentEvent->getOriginalLine());
            $competition->addEvent($event);
            break;
        case 'result':
            if(!$competition->hasCurrentEvent()) continue;
            $name = $competitionParser->getNameFromLine($line);
            $yearOfBirth = $competitionParser->getYearOfBirthFromLine($line);
            $times = $competitionParser->getTimesFromLine($line);
            $result = Result::create($name, $yearOfBirth, $times, $line);

            $competition->addResultToCurrentEvent($result);
            break;
    }
}

$competition->removeNullEvents();

try {
     printCompetition($competition, 'template');
   $dbHelper = new DbHelper();
   $dbHelper->saveCompetitionToDatabase($competition);
} catch (Exception $e) {
    print_r('Something terrible happened' . PHP_EOL);
    print_r($e->getMessage());
}