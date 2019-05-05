<?php
define("RESULTS_DIR", 'competitions/');

include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = $argv[1];
$config = yaml_parse_file($config);

$competition = new Competition($config['name'], $config['date'], $config['location'], $config['clock_type']);

$fileName = RESULTS_DIR . $config['file'];

switch (pathinfo($fileName, PATHINFO_EXTENSION)) {
    case 'csv':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('FILETYPE', 'csv');
        define('ENCODING', "UTF-8");
        break;
    case 'pdf':
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fileName);
        $text = $pdf->getText();
        $lines = explode("\n", $text);
        define('FILETYPE', 'pdf');
        define('ENCODING', "UTF-8");
        break;
    case 'txt':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('FILETYPE', 'txt');
        define('ENCODING', "UTF-8");
        break;
    default:
        print_r('Unknown filetype ' . pathinfo($fileName, PATHINFO_EXTENSION));
        exit;
        break;
}

$competitionParser = new CompetitionParser($config);
$lines = $competitionParser->createUsableLines($lines);
$lines = $competitionParser->cleanLines($lines);

writeToFile($lines);

$i = 1;
foreach ($lines as $line) {
    $lineType = $competitionParser->getLineType($line);
    switch ($lineType) {
        case 'event':
            $eventId = $competitionParser->getEventIdFromLine($line);
            $gender = $competitionParser->getGenderFromLine($line);
            $includeEvent = $competitionParser->shouldIncludeEvent($line);
            $roundNumber = $competitionParser->getRoundFromLine($line);
            $event = Event::create($eventId, $gender, $includeEvent, $line, $roundNumber);
            $competition->addEvent($event);
            break;
        case 'gender':
            $gender = $competitionParser->getGenderFromLine($line);
            $events = $competition->getEvents();

            /** @var Event $currentEvent */
            $currentEvent = end($events);
            if (is_null($currentEvent) || !$currentEvent) continue;

            $event = Event::create($currentEvent->getId(), $gender, true, $currentEvent->getOriginalLine());
            $competition->addEvent($event);
            break;
        case 'result':
            if (!$competition->hasCurrentEvent()) continue;
            $name = $competitionParser->getNameFromLine($line);
            $yearOfBirth = $competitionParser->getYearOfBirthFromLine($line);
            if(($isDq = $competitionParser->isDq($line))) {
                $times = ['59:59.99'];
            } else {
                $times = $competitionParser->getTimesFromLine($line);
            }
            $result = Result::create($name, $yearOfBirth, $times, $isDq, $line);

            $competition->addResultToCurrentEvent($result);
            break;
        case 'round':
            if (!$competition->hasCurrentEvent()) continue;
            $roundNumber = $competitionParser->getRoundFromLine($line);
            $currentEvent = $competition->getCurrentEvent();
            $event = Event::create($currentEvent->getId(), $currentEvent->getGender(), true, $currentEvent->getOriginalLine(), $roundNumber);
            $competition->addEvent($event);
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