<?php namespace CompetitionParser\Classes\Helpers;

use PHPStan\Command\AnalyseApplication;

class CompetitionParser
{
    protected $config;
    private $nameRegex;
    private $firstNameRegex;
    private $lastNameRegex;
    private $timeRegex;
    private $dqTimes;
    private $dnsTimes = [];
    public $multipleResultsPerLine;
    public $multipleRoundsPerLine;
    private $yobRegex;
    private $timeIndex;
    private $nameIndex;
    private $lineConversion;
    private $nameConversion;
    private $textConversions;
    private $rounds;
    private $utf8Encoded;
    private $mirrorTimes;
    private $commaOrSpaceExplode;
    private $invalidTimes;
    private $csvDelimiter;

    public $csvNameIndexes;
    private $csvTimeIndex;
    public $csvYobIndex;
    private $csvNationalityIndex;
    private $csvGenderIndex;

    public function __construct($config)
    {
        $this->config = yaml_parse_file('config.yaml');
        $this->config = array_merge($this->config, $config);
        define("PARSE_YOB", intval($this->config['parser_config']['formats']['yob_format']) > 0);
        define("IGNORE_YOB_NOT_FOUND", isset($this->config['parser_config']['ignore_yob_not_found']) ? intval($this->config['parser_config']['ignore_yob_not_found']) : 0);
        define("IGNORE_DQ", isset($this->config['parser_config']['ignore_dq']) ? intval($this->config['parser_config']['ignore_dq']) : 0);
        define("SEPARATE_GENDER", intval($this->config['parser_config']['separate_gender']) > 0);

        $this->nameRegex = isset($this->config['parser_config']['formats']['name_format']) ? $this->config['regex']['name'][$this->config['parser_config']['formats']['name_format']] : false;
        $this->firstNameRegex = isset($this->config['parser_config']['formats']['first_name_format']) ? $this->config['regex']['first_name'][$this->config['parser_config']['formats']['first_name_format']] : false;
        $this->lastNameRegex = isset($this->config['parser_config']['formats']['last_name_format']) ? $this->config['regex']['last_name'][$this->config['parser_config']['formats']['last_name_format']] : false;
        $this->multipleResultsPerLine = isset($this->config['parser_config']['multiple_results_per_line']) ? boolval($this->config['parser_config']['multiple_results_per_line']) : true;
        $this->multipleRoundsPerLine = isset($this->config['parser_config']['multiple_rounds_per_line']) ? boolval($this->config['parser_config']['multiple_rounds_per_line']) : true;
        $this->invalidTimes = isset($this->config['invalid_times']) ? $this->config['invalid_times'] : [];



        $this->timeIndex = $this->config['parser_config']['time_index'];
        $this->nameIndex = isset($this->config['parser_config']['name_index']) ? $this->config['parser_config']['name_index'] : 0;
        $this->timeRegex = $this->config['regex']['time'][$this->config['parser_config']['formats']['time_format']];
        $this->dqTimes = isset($this->config['dq_times']) ? $this->config['dq_times'] : [];
        $this->lineConversion = $this->config['parser_config']['line_conversion'];
        $this->nameConversion = $this->config['parser_config']['name_conversion'];
        $this->textConversions = isset($this->config['parser_config']['text_conversions']) ? $this->config['parser_config']['text_conversions'] : [];
        $this->rounds = isset($this->config['parser_config']['rounds']) ? $this->config['parser_config']['rounds'] : [];
        $this->utf8Encoded = isset($this->config['parser_config']['utf8_encoded']) ? boolval($this->config['parser_config']['utf8_encoded']) : false;
        $this->mirrorTimes = isset($this->config['parser_config']['mirror_times']) ? boolval($this->config['parser_config']['mirror_times']) : false;
        $this->commaOrSpaceExplode = isset($this->config['parser_config']['name_comma_or_space_explode']) ? boolval($this->config['parser_config']['name_comma_or_space_explode']) : false;
        if (PARSE_YOB) {
            $this->yobRegex = $this->config['regex']['yob'][$this->config['parser_config']['formats']['yob_format']];
        }

        if (FILETYPE === 'csv' || FILETYPE === 'dir') {
            $this->csvNameIndexes = $this->config['parser_config']['csv']['name'];
            $this->csvTimeIndex = $this->config['parser_config']['csv']['time'];
            $this->csvYobIndex = $this->config['parser_config']['csv']['yob'];
            $this->csvNationalityIndex = $this->config['parser_config']['csv']['nationality'];
            $this->csvGenderIndex = $this->config['parser_config']['csv']['gender'];
            $this->csvDelimiter = $this->config['parser_config']['csv']['delimiter'] ?? ',';
        }

    }

    /**
     * @param string $line
     * @return string
     */
    public function getLineType($line): string
    {
        if ($this->lineContains($line, $this->config['event_stopper'])) {
            return 'stop-event';
        }
        if ($this->lineContains($line, $this->config['event_signifiers'])
            && !$this->lineContains($line, $this->config['event_designifiers'])) {
            return 'event';
        }
        if ($this->hasValidResult($line) && $this->hasName($line) && (!PARSE_YOB || IGNORE_YOB_NOT_FOUND || $this->getYearOfBirthFromLine($line) !== 'unknown')) {
            return 'result';
        }
        if ($this->getEventGenderFromLine($line) && $this->config['parser_config']['separate_gender']) {
            return 'gender';
        }
        if (!is_null($this->rounds) && $this->lineContains($line, $this->config['round_signifiers']) && !$this->lineContains($line, $this->config['round_designifiers'])) {
            return 'round';
        }
        return '';
    }

    /**
     * @param $line
     * @return int
     */
    public function getEventIdFromLine($line): int
    {
        $disciplines = $this->config['disciplines'];

        $discipline = 0;
        foreach ($disciplines as $eventId) {
            foreach ($eventId as $description) {
                $description = str_replace('/', '\/', $description);
                if (preg_match('/' . $description . '/', $line)) {
                    $discipline = array_search($eventId, $disciplines);
                }
            }
        }

        return $discipline;
    }

    /**
     * @param $line
     * @return string
     */
    public function getEventGenderFromLine($line): string
    {
        if ($this->lineContains($line, $this->config['genders']['female_signifiers'])) {
            return 2;
        }
        if ($this->lineContains($line, $this->config['genders']['male_signifiers'])) {
            return 1;
        }
        return 0;
    }

    /**
     * @param $line
     * @return string
     */
    public function getResultGenderFromLine($line): string
    {
        if (FILETYPE === 'csv' || FILETYPE === 'dir') {
            $csv = str_getcsv($line, $this->csvDelimiter);
            $gender = trim($csv[$this->csvGenderIndex]);
            if ($gender === 'F') {
                return 2;
            }
            if ($gender === 'M') {
                return 1;
            }
        }
        return 0;
    }

    /**
     * checks if any of the values in array occurs in string
     * @param string $line
     * @param array $needles
     * @return bool
     */
    public function lineContains($line, array $needles)
    {
        foreach ($needles as $needle) {
            if (stripos($line, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array $lines
     * @return array
     */
    public function createUsableLines($lines)
    {
        if(is_array($this->lineConversion)) {
            foreach ($this->lineConversion as $conversionType) {
                $lines = $this->convertLines($lines, strval($conversionType));
            }
            return $lines;
        }

        return $this->convertLines($lines, strval($this->lineConversion));
    }

    public function convertLines($lines, $type)
    {
        switch ($type) {
            case 'name-yob-club-time':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    if (!$this->hasValidResult($line)) {
                        $newLines[] = $line;
                        $i++;
                        continue;
                    }
                    $resultLine = '';
                    $resultLine .= $lines[$i - 3] . "   ";
                    $resultLine .= $lines[$i - 2] . "   ";
                    $resultLine .= $lines[$i - 1] . "   ";
                    $resultLine .= $lines[$i] . "   ";
                    $newLines[] = $resultLine;
                    $i++;
                }
                return $newLines;
                break;
            case 'name-yobclub-time':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    if (!$this->hasValidResult($line)) {
                        $newLines[] = $line;
                        $i++;
                        continue;
                    }
                    $resultLine = '';
                    $resultLine .= $lines[$i - 2] . "   ";
                    $resultLine .= $lines[$i - 1] . "   ";
                    $resultLine .= $lines[$i] . "   ";
                    $newLines[] = $resultLine;
                    $i++;
                }
                return $newLines;
                break;
            case 'name-time':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    if (!$this->hasValidResult($line)) {
                        $newLines[] = $line;
                        $i++;
                        continue;
                    }
                    $resultLine = '';
                    $resultLine .= $lines[$i - 1] . "   ";
                    $resultLine .= $lines[$i] . "   ";
                    $newLines[] = $resultLine;
                    $i++;
                }
                return $newLines;
                break;
            case 'time-name':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    if (!$this->hasValidResult($line)) {
                        $newLines[] = $line;
                        $i++;
                        continue;
                    }
                    $resultLine = '';
                    $resultLine .= $lines[$i] . "   ";
                    $resultLine .= $lines[$i + 1] . "   ";
                    $newLines[] = $resultLine;
                    $i++;
                }
                return $newLines;
                break;
            case 'club-time-name':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    if (!$this->hasValidResult($line)) {
                        $newLines[] = $line;
                        $i++;
                        continue;
                    }
                    $resultLine = '';
                    if($this->getLineType($lines[$i + 1]) !== 'event') {
                        $resultLine .= $lines[$i + 1] . "   ";
                    }
                    $resultLine .= $lines[$i] . "   ";
                    $resultLine .= $lines[$i - 1] . "   ";
                    $newLines[] = $resultLine;
                    $i++;
                }
                return $newLines;
            case 'striptags':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    $newLines[] = strip_tags($line);
                    $i++;
                }
                return $newLines;
            case 'remove-nicknames':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    $newLines[] = preg_replace('/\s\([^\.]+\)\s/', ' ', $line);
                    $i++;
                }
                return $newLines;
                break;
            case 'w-space':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    $newLines[] = preg_replace('/(?<=W) (?=[A-z])/', '', $line);
                    $i++;
                }
                return $newLines;
            case 'name-yob-club-time-time':
                $newLines = [];
                $i = 0;
                foreach ($lines as $line) {
                    if (!$this->hasValidResult($line)) {
                        $newLines[] = $line;
                        $i++;
                        continue;
                    }
                    $resultLine = '';
                    $resultLine .= $lines[$i - 3] . "   ";
                    $resultLine .= $lines[$i - 2] . "   ";
                    $resultLine .= $lines[$i - 1] . "   ";
                    $resultLine .= $lines[$i] . "   ";
                    $resultLine .= $lines[$i + 1];
                    $lines[$i + 1] = '';
                    $newLines[] = $resultLine;
                    $i++;
                }
                return $newLines;
                break;
            case 'jauswertung':
                return moveEventsInJauswertung($lines, $this);
                break;
            case 'move-events-spain-to-top':
                $i = 0;
                foreach ($lines as $line) {
                    if ($this->getEventIdFromLine($line)) {
                        for($j = $i - 1; ; $j--) {
                            if ($j < 0) {
                                print_r('line conversion fail for line: ' . $i . $line . PHP_EOL);
                                exit;
                            }
//                            if (preg_match('/^1(?![0-9])/', substr($lines[$j], 0, 2))) {
//                            if (preg_match('/^Juv/', substr($lines[$j], 0, 3))) {
                            if (preg_match('/^Torrevieja/', $lines[$j])) {
                                $lines[$j] = $line;
                                print_r('moved line ' . $i . ' to ' . $j . PHP_EOL);
                                break;
                            }
                        }
                    }
                    $i++;
                }
                return $lines;
                break;
            default:
                return $lines;
        }
    }



    public function createUsableText(string $text)
    {
        // replace nbsp charcter with regular space
        $text = preg_replace('/ /', ' ', $text);

        foreach ($this->textConversions as $textConversion) {

            switch ($textConversion) {
                case 'new-line-after-time':
                    $text = preg_replace('/(?<=' . $this->timeRegex . ')/', PHP_EOL, $text);
                    break;
                case 'event-on-time-line':
                    $text = preg_replace('/(?<=[0-9]{2}\.[0-9]{2})\s+(?=Event)/', PHP_EOL, $text);
                    break;
                case 'same-line-record':
                    $text = preg_replace('/(?<=[A-Z]{2})\t(?=[0-9][\s[A-z]])/', '\n', $text);
                    break;
            }
        }
        return $text;
    }


    public function cleanLines(array $lines)
    {
        $i = 0;
        foreach ($lines as $line) {
            $line = str_replace('&#39;', "'", $line);
//            $line = preg_replace('/(?<=[A-Z])\h(?=[A-Z][^a-z])/u', '', $line);

            if ($this->utf8Encoded) {
                $line = utf8_decode($line);
            }
            $lines[$i] = $line;
            $i++;
        }
        return $lines;
    }

    /**
     * @param $line
     * @return bool
     */
    public function hasValidResult($line)
    {
        $hasResult = preg_match('/' . $this->timeRegex . '/', $line);
        $isDq = $this->lineContains($line, $this->config['dq_signifiers']);
        $isDns = $this->lineContains($line, $this->config['dns_signifiers']);
        $isValid = !$this->lineContains($line, $this->config['result_rejectors']);
        return ($hasResult || $isDq || $isDns) && $isValid;
    }

    public function getNameFromLine($line)
    {
        if (FILETYPE == 'csv' || FILETYPE == 'dir') {
            $names = [];
            $csv = str_getcsv($line, $this->csvDelimiter);
            foreach ($this->csvNameIndexes as $nameIndex) {
                $names[] = trim($csv[$nameIndex]);
            }
            return implode(' ', $names);
        } elseif(isset($this->config['parser_config']['formats']['first_name_format']) &&
            isset($this->config['parser_config']['formats']['last_name_format'])
        ) {
            return trim($this->getFirstNameFromLine($line) . ' ' . $this->getLastNameFromLine($line));
        } elseif (isset($this->config['parser_config']['formats']['name_format'])) {
            return $this->getFullNameFromLine($line);
        }
        return null;
    }

    /**
     * @param $line
     * @return string
     */
    public function getFullNameFromLine($line)
    {
        $matches = array();
        preg_match_all($this->nameRegex, $line, $matches);

        if(!isset($matches[0][$this->nameIndex])) {
            return null;
        }

        $name = $matches[0][$this->nameIndex];

        if (strpos($name, ',') !== false) {
            $nameParts = explode(',', $name);
            $nameParts = array_map('trim', $nameParts);
            $nameParts = array_reverse($nameParts);
            $name = implode(' ', $nameParts);
        } elseif ($this->commaOrSpaceExplode == true) {
            $nameParts = explode(' ', $name);
            $nameParts = array_map('trim', $nameParts);
            $nameParts = array_reverse($nameParts);
            $name = implode(' ', $nameParts);
        }

        $name = preg_replace("/\-{2,}/", "", $name); // remove multiple -
        $name = preg_replace("/\s{2,}/", " ", $name); // replace multiple space with one

        return trim($name);
    }

    public function getFirstNameFromLine($line)
    {
        $matches = array();
        preg_match_all($this->firstNameRegex, $line, $matches);
        if(!isset($matches[0][0])) return '';

        $name = $matches[0][0];

        $name = preg_replace("/\s{2,}/", " ", $name); // replace multiple space with one

        return trim($name);
    }

    public function getLastNameFromLine($line)
    {
        $matches = array();
        preg_match_all($this->lastNameRegex, $line, $matches);
        if(!isset($matches[0][0])) return '';

        $name = $matches[0][0];

        $name = preg_replace("/\s{2,}/", " ", $name); // replace multiple space with one

        return trim($name);
    }

    public function hasName(string $line)
    {
        return strlen($this->getNameFromLine($line)) > 0;
    }

    /**
     * @param string line
     * @return string
     */
    public function getYearOfBirthFromLine($line)
    {
        if (!PARSE_YOB) return 'unknown';

        if ((FILETYPE == 'csv'  || FILETYPE == 'dir') && $this->csvYobIndex) {
            $csv = str_getcsv($line, $this->csvDelimiter);
            return $csv[$this->csvYobIndex];
        }

        $matches = array();
        preg_match($this->yobRegex, $line, $matches);
        if (!$matches) {
//            print_r($line . PHP_EOL);
            return 'unknown';
        }
        $yearOfBirth = preg_replace('/[^0-9]/', '', $matches[0]);
        $yearOfBirth = intval($yearOfBirth);


        if ($yearOfBirth < 100) $yearOfBirth += ($yearOfBirth < 19 ? 2000 : 1900);
        return $yearOfBirth;
    }

    /**
     * @param string $line
     * @return array
     */
    public function getTimesFromLine($line)
    {
        if (FILETYPE == 'csv' || FILETYPE == 'dir') {
            $csv = str_getcsv($line, $this->csvDelimiter);
            return str_replace('""', '"', [$csv[$this->csvTimeIndex]]);
        }

        $times = array();
        preg_match_all('/' . $this->timeRegex . '/', $line, $times);
        if (!isset($times[0])) {
            return [];
        }
        $times = $times[0];
        if ($this->isDq($line)) {
            $times[] = '59:59.99';
        }
        switch ($this->timeIndex) {
            case 'all':
                if ($this->mirrorTimes) {
                    return array_reverse($times);
                }
                return $times;
                break;
            case 'first':
                return [current($times)];
                break;
            case 'last':
                return [end($times)];
                break;
            default:
                $index = intval($this->timeIndex);
                return [$times[$index]];
                break;
        }
    }

    /**
     * @param $line
     */
    public function getNationalityFromLine($line)
    {
        $nationality = null;
        if ($this->csvNationalityIndex && (FILETYPE == 'csv' || FILETYPE == 'dir')) {
            $csv = str_getcsv($line, $this->csvDelimiter);
            $nationality = $csv[$this->csvNationalityIndex];
        }
        return $nationality;
    }

    /**
     * @param $line
     * @return bool
     */
    public function shouldIncludeEvent($line)
    {
        return !$this->lineContains($line, $this->config['event_rejectors']);
    }

    public function getRoundFromLine($line)
    {
        foreach ($this->rounds as $roundNumber => $roundSignifier) {
            if ($this->lineContains($line, [$roundSignifier])) {
                return $roundNumber;
            }
        }
        return 0;
    }

    public function isDq($line)
    {
        return $this->lineContains($line, $this->config['dq_signifiers']);
    }


    public function isDns($line)
    {
        return $this->lineContains($line, $this->config['dns_signifiers']);
    }

    public function timeIsDq($time)
    {
        return $this->lineContains($time, $this->dqTimes);
    }

    public function timeIsDns($time)
    {
        return $this->lineContains($time, $this->dnsTimes);
    }

    public function nameConversion(string $name)
    {
        switch ($this->nameConversion) {
            case 'remove-yob-move-last-part-to-front': # FABRE Margaux (1992)
                $name = trim(preg_replace('/\([0-9]{4}\)/', '', $name));
                $nameParts = explode(' ', $name);
                $firstName = array_pop($nameParts);
                array_unshift($nameParts, $firstName);
                return implode(" ", $nameParts);
            break;
            default:
                return $name;
                break;
        }
    }

    public function timeIsValid($time)
    {
        return !in_array($time, $this->invalidTimes);
    }
}