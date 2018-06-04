<?php
$config = include('config.php');

const EVENT_TYPE_SPLASH = 'splash';
const EVENT_TYPE_GERMAN = 'german';

/**
 * @param string $line
 * @param string $type
 * @return string
 */
function getLineType($line, $type)
{
    switch ($type) {
        case EVENT_TYPE_SPLASH:
            if (contains($line, $GLOBALS['config']['parser']['splash']['event_signifiers'])
                && !contains($line, $GLOBALS['config']['parser']['splash']['event_designifiers'])) {
                return 'event';
            } elseif (hasValidResult($line)) return 'result';
            break;
        case EVENT_TYPE_GERMAN:
            if (contains($line, $GLOBALS['config']['parser']['german']['event_signifiers'])
                && !contains($line, $GLOBALS['config']['parser']['german']['event_designifiers'])) {
                return 'event';
            } elseif (hasValidResult($line)) return 'result';
            break;
    }
    return '';
}

/**
 * @param string $line
 * @return bool
 */
function hasValidResult($line)
{
    switch (EVENT_TYPE) {
        case EVENT_TYPE_SPLASH:
            $hasResult = preg_match("/[0-9]{2}\.[0-9]{2}/", $line);
            $isValid = !contains($line, $GLOBALS['config']['parser']['splash']['result_rejectors']);
            return $hasResult && $isValid;
            break;
        case EVENT_TYPE_GERMAN:
            $hasResult = preg_match("/[0-9]{2},[0-9]{2}/", $line);
            $isValid = !contains($line, $GLOBALS['config']['parser']['german']['result_rejectors']);
            return $hasResult && $isValid;
            break;
        default:
            return false;
    }
}

/**
 * @param $line
 * @return int
 */
function getEventId($line)
{
    $disciplines = $GLOBALS['config']['parser']['disciplines'];
    $discipline = 0;
    foreach ($disciplines as $eventId) {
        foreach ($eventId as $description) {
            if (stristr($line, $description)) {
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
function getGender($line)
{
    if (contains($line, $GLOBALS['config']['parser']['genders']['female_signifiers'])) return 2;
    elseif (contains($line, $GLOBALS['config']['parser']['genders']['male_signifiers'])) return 1;
    return 0;
}

/**
 * @param string $line
 * @return string
 */
function getFirstNameFromLine($line)
{
    switch (EVENT_TYPE) {
        case EVENT_TYPE_SPLASH:
            $matches = array();
            preg_match('/(\s?[A-Z][a-z\x{0040}-\x{00ff}]+-?)+/', utf8_decode($line), $matches);
            return trim(utf8_encode($matches[0]));
            break;
        case EVENT_TYPE_GERMAN:
            $name = array();
            preg_match('/(\s?[A-Za-z\x{0040}-\x{00ff}]+-?)+\s?,\s([A-Za-z\x{00e0}-\x{00ff}]+-?)+/', utf8_decode($line), $name);
            $name = trim($name[0]);
            $firstName = substr($name,  strpos($name, ',') + 1);
            return trim($firstName);
            break;
        default:
            return '';
            break;
    }
}

/**
 * @param string $line
 * @return string
 */
function getLastNameFromLine($line)
{
    switch (EVENT_TYPE) {
        case EVENT_TYPE_SPLASH:
            $matches = array();
            preg_match('/(\s\'?[a-z]+)*((\s?[A-Z\x{00C0}-\x{00DF}]{2,}\s?)+([\']\w+\s)?-?)+/', utf8_decode($line), $matches);
            return trim(utf8_encode($matches[0]));
            break;
        case EVENT_TYPE_GERMAN:
            $name = array();
            preg_match('/(\s?[A-Za-z\x{0040}-\x{00ff}]+-?)+\s?,\s([A-Za-z\x{00e0}-\x{00ff}]+-?)+/', utf8_decode($line), $name);
            $name = trim($name[0]);
            $lastName = substr($name, 0, strpos($name, ','));
            return $lastName;
            break;
        default:
            return '';
            break;
    }
}

/**
 * @param string line
 * @return string
 */
function getYearOfBirthFromLine($line)
{
    switch (EVENT_TYPE) {
        case EVENT_TYPE_SPLASH:
            $matches = array();
            preg_match('/\s[0-9]{2}\s/', $line, $matches);
            return trim($matches[0]); // in 2 digits
            break;
        case EVENT_TYPE_GERMAN:
            $matches = array();
            preg_match('/\s[0-9]{4}\s/', $line, $matches);
            return substr(trim($matches[0]), 2); // in 2 digits
            break;
        default:
            return '';
            break;
    }
}

/**
 * @param string $line
 * @return array
 */
function getTimesFromLine($line)
{
    switch (EVENT_TYPE) {
        case EVENT_TYPE_SPLASH:
            $times = array();
            preg_match('/[0-9]{0,2}[:]?[0-9]{1,2}[.][0-9]{2}/', $line, $times);
            return $times;
            break;
        case EVENT_TYPE_GERMAN:
            $times = array();
            preg_match('/[0-9]{1}:[0-9]{2}[,][0-9]{2}/', $line, $times);
            $i = 0;
            foreach ($times as $time) {
                $times[$i] = str_replace(',', '.', $time);
                $i++;
            }
            return isset($times[0]) ? $times : [];
            break;
        default:
            return[];
            break;
    }
}

/**
 * checks if any of the values in array occurs in string
 * @param string $haystack
 * @param array $needles
 * @return bool
 */
function contains($haystack, array $needles)
{
    foreach ($needles as $needle) {
        if (stripos($haystack, $needle) !== false) return true;
    }
    return false;
}

/**
 * Scan the api path, recursively including all PHP files
 *
 * @param string $dir
 * @param int $depth (optional)
 */
function require_all($dir, $depth = 0)
{
    // require all php files
    $scan = glob("$dir/*");
    foreach ($scan as $path) {
        if (preg_match('/\.php$/', $path)) {
            require_once $path;
        } elseif (is_dir($path)) {
            $this->_require_all($path, $depth + 1);
        }
    }
}

/**
 * @param string $text
 * @return string
 */
function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return $text;
}

/**
 * @param $time
 * @return mixed|string
 * @throws Exception
 */
function toSqlInterval($time)
{
    $time = str_replace(',', '.', $time);
    if (strlen($time) == 5) {
        $time = '00:00:' . $time;
    } elseif (strlen($time) == 7) {
        $time = '00:0' . $time;
    } elseif (strlen($time) == 8) {
        $time = '00:' . $time;
    } else {
        throw new Exception("Couldn't parse time");
    }
    return $time;
}

/**
 * @param Competition $competition
 */
function printCompetition($competition)
{

    print_r($GLOBALS['config']['competition']);
    sleep(1);
    foreach ($competition->getEvents() as $event) {
        print_r($GLOBALS['config']['parser']['disciplines'][$event->getId()][0] . " " . $event->getGenderName() . PHP_EOL);
        usleep(400000);
    }
    usleep(400000);
    foreach ($competition->getEvents() as $event) {
        print_r(PHP_EOL);
        print_r($GLOBALS['config']['parser']['disciplines'][$event->getId()][0] . " " . $event->getGenderName() . PHP_EOL);
        usleep(400000);
        foreach ($event->getResults() as $result) {
            if(is_null($result)) var_dump($result);
            print_r($result->getYearOfBirth() . " " . $result->getFirstName() . " " . $result->getLastName() . " " . $result->getFirstTime() . PHP_EOL);
            usleep(200000);
        }
    }
}
