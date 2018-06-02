<?php
include 'vendor/autoload.php';
include '_functions.php';

require_all('classes');

$config = include('config.php');

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
    }
}
try {
    printCompetition($competition);
//    $dbHelper = new DbHelper();
//    $dbHelper->saveCompetitionToDatabase($competition);
} catch (Exception $e) {
    print_r('Something terrible happend' . PHP_EOL);
    print_r($e->getMessage());
}