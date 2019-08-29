<?php namespace CompetitionParser;

use CompetitionParser\Classes\Helpers\CompetitionParser;
use CompetitionParser\Classes\Helpers\DbHelper;
use CompetitionParser\Classes\Models\Competition;
use CompetitionParser\Classes\Models\Event;
use CompetitionParser\Classes\Models\IndividualResult;
use CompetitionParser\Classes\Models\Athlete;
use thiagoalessio\TesseractOCR\TesseractOCR;

define("RESULTS_DIR", 'competitions/');

include 'vendor/autoload.php';
include '_functions.php';
require_all('classes');


$config = $argv[1];
$config = yaml_parse_file($config);

DbHelper::bootEloquent();

$competition = Competition::where('name', $config['name'])->first();

if(!$competition) {
    print_r('ADD COMPETITION ' . $config['name']);
    exit;
}

$fileName = __DIR__ . DIRECTORY_SEPARATOR . RESULTS_DIR . $config['file'];

$fileType = pathinfo($fileName, PATHINFO_EXTENSION);
$fileType = is_dir($fileName) ? 'dir' : $fileType;
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
    case 'lxf':
    case 'lef':
        $reader = new \leonverschuren\Lenex\Reader();
        $parser = new \leonverschuren\Lenex\Parser();
        $result = $parser->parseResult($reader->read($fileName));
        break;
    case 'dir':
        $files = array_diff(scandir($fileName), array('.', '..'));

        foreach ($files as $file) {
            $lines[] = $file;
            $lines = array_merge($lines, file($fileName . '/' . $file, FILE_IGNORE_NEW_LINES));
        }
        break;
    default:
        print_r('Unknown filetype ' . $fileType);
        exit;
        break;
}

if ($fileType == 'lxf' || $fileType == 'lef') {
    $events = [];
    foreach ($result->getMeets() as $meet) {
        foreach ($meet->getSessions() as $session) {
            foreach ($session->getEvents() as $event) {
                $eventName = $event->getSwimStyle()->getName();
                $eventId = $competitionParser->getEventIdFromLine($eventName);
                if (!$eventId) {
                    print_r('Could not find event id for ' . $eventName . PHP_EOL);
                }
                $events[$event->getEventId()] = $eventId;
            }
        }
    }

    $dbHelper = new DbHelper();
    $dbHelper->saveLenexCompetition($result, $events, $competition);
} else {
    $lines = $competitionParser->createUsableLines($lines, $competitionParser);
    $lines = $competitionParser->cleanLines($lines);

    writeToFile($lines);

    $i = 1;
    $totalLines = count($lines);
    $classification = 1;
    /** @var Event $currentEvent */
    $currentEvent = null;
    $unparsableEventLines = [];
    foreach ($lines as $line) {
        print_r($i . '/' . $totalLines . PHP_EOL);
        $lineType = $competitionParser->getLineType($line);

        switch ($lineType) {
            case 'event':
                $eventId = $competitionParser->getEventIdFromLine($line);
                $gender = $competitionParser->getGenderFromLine($line);
                $includeEvent = $competitionParser->shouldIncludeEvent($line);
                $roundNumber = $competitionParser->getRoundFromLine($line);
                $currentEvent = Event::create($eventId, $gender, $includeEvent, $line, $roundNumber);

                if(!$currentEvent) {
                    $unparsableEventLines[] = $line;
                }

                $classification = 1;
                break;
            case 'gender':
                $gender = $competitionParser->getGenderFromLine($line);

                if (is_null($currentEvent) || !$currentEvent) continue;

                $currentEvent = Event::create($currentEvent->getId(), $gender, true, $currentEvent->getOriginalLine());
                break;
            case 'result':
                if (is_null($currentEvent)) continue;
                $lineIsDns = false;

                $name = $competitionParser->getNameFromLine($line);
                $yearOfBirth = $competitionParser->getYearOfBirthFromLine($line);
                $nationality = $competitionParser->getNationalityFromLine($line);
                $times = $competitionParser->getTimesFromLine($line);
                if ((($lineIsDq = $competitionParser->isDq($line)) || ($lineIsDns = $competitionParser->isDns($line))) && (!$times || !$competitionParser->multipleResultsPerLine)) {
                    $times = ['59:59.999'];
                }

                $round = $currentEvent->getRoundNumber();
                $athlete = Athlete::findOrCreate($name, $currentEvent->getGender(), $yearOfBirth, $nationality);
                print_r($name . ' | ' . $currentEvent->getGender() . ' | ' . $yearOfBirth . PHP_EOL);
                foreach ($times as $time) {
                    $timeIsDq = $competitionParser->timeIsDq($time);
                    if ($lineIsDq && IGNORE_DQ) {
                        continue;
                    }
                    $timeIsDns = $competitionParser->timeIsDns($time);
                    if ($timeIsDns || $timeIsDq) {
                        $time = '59:59.99';
                    }

                    $result = new IndividualResult();
                    $result->athlete_id = $athlete->id;
                    $result->event_id = $currentEvent->getId();
                    $result->competition_id = $competition->id;
                    $result->time = toSqlInterval($time);
                    $result->points = 0;
                    $result->original_line = $line;
                    $result->round = $round;
                    $result->disqualified = $lineIsDq;
                    $result->did_not_start = $lineIsDns;
                    $result->save();


                    $round++;
                }

                $classification++;

                break;
            case 'round':
                if (!is_null($currentEvent)) continue;
                $roundNumber = $competitionParser->getRoundFromLine($line);
                break;
            case 'stop-event':
                $currentEvent = null;
                break;
        }
        $i++;
    }

    if ($unparsableEventLines) {
        print_r('Unparsable events:' . PHP_EOL);
        print_r($unparsableEventLines);
    }
}