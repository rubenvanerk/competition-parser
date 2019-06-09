<?php
use thiagoalessio\TesseractOCR\TesseractOCR;

define("RESULTS_DIR", 'competitions/');

include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = $argv[1];
$config = yaml_parse_file($config);

$competition = new Competition($config['name'], $config['date'], $config['location'], $config['clock_type']);

$fileName = __DIR__ . DIRECTORY_SEPARATOR . RESULTS_DIR . $config['file'];

$fileType = pathinfo($fileName, PATHINFO_EXTENSION);
define('FILETYPE', $fileType);
$competitionParser = new CompetitionParser($config);

switch ($fileType) {
    case 'csv':
        $lines = file($fileName, FILE_IGNORE_NEW_LINES);
        define('ENCODING', "UTF-8");
        break;
    case 'pdf':
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fileName);
        $text = $pdf->getText();
        $text = $competitionParser->createUsableText($text);
        $lines = explode("\n", $text);
        define('ENCODING', "UTF-8");
        break;
    case 'txt':
        $text = file_get_contents($fileName);
        $text = $competitionParser->createUsableText($text);
        $lines = explode("\n", $text);
        define('ENCODING', "UTF-8");
        break;
    case 'html':
        $text = file($fileName, FILE_IGNORE_NEW_LINES);
        define('ENCODING', "UTF-8");
        break;
    case 'tiff':
        $text = (new TesseractOCR($fileName))->lang('ita')->run();
        $lines = explode("\n", $text);
        break;
    default:
        print_r('Unknown filetype ' . pathinfo($fileName, PATHINFO_EXTENSION));
        exit;
        break;
}

$lines = $competitionParser->createUsableLines($lines);
$lines = $competitionParser->cleanLines($lines);

writeToFile($lines);

$i = 1;
$classification = 1;
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
            $classification = 1;
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
            $times = $competitionParser->getTimesFromLine($line);
            if((($isDq = $competitionParser->isDq($line)) || ($isDns = $competitionParser->isDns($line))) && (!$times || !$competitionParser->multipleResultsPerLine)) {
                $times = ['59:59.99'];
            }

            $round = 0;
            foreach ($times as $time) {
                $isDq = $competitionParser->timeIsDq($time);
                if ($isDq && IGNORE_DQ) {
                    continue;
                }
                $isDns = $competitionParser->timeIsDns($time);
                if($isDns || $isDq) {

                    $time = '59:59.99';
                }
                $result = Result::create($name, $yearOfBirth, [$time], $isDq, $isDns, $line, $round, $classification);
                $competition->addResultToCurrentEvent($result);
                $round++;
            }

            $classification++;

            break;
        case 'round':
            if (!$competition->hasCurrentEvent()) continue;
            $roundNumber = $competitionParser->getRoundFromLine($line);
            $currentEvent = $competition->getCurrentEvent();
            $event = Event::create($currentEvent->getId(), $currentEvent->getGender(), true, $currentEvent->getOriginalLine(), $roundNumber);
            $competition->addEvent($event);
            break;
        case 'stop-event':
            $competition->addEvent(null);
            break;
    }
    $i++;
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