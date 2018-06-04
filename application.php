<?php
include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = include('config.php');

define("EVENT_TYPE", $config['competition']['type']);

$competition = new Competition();
$currentEventAvailable = false;

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($config['pdf_folder'] . $config['competition']['filename']);

$lines = explode("\n", $pdf->getText());
foreach ($lines as $line) {
    $lineType = getLineType($line, $config['competition']['type']);
    switch ($lineType) {
        case 'event':
            $event = Event::createFromLine($line);
            $success = $competition->addEvent($event);
            $currentEventAvailable = $success;
            break;
        case 'result':
            if(!$currentEventAvailable) continue;
            $result = Result::createFromLine($line);
            $competition->addResultToCurrentEvent($result);
            break;
        default:
//            print_r('Could not parse this line: ' . $line . PHP_EOL);
//            sleep(1);
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