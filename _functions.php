<?php
$config = include('config.php');

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
            require_all($path, $depth + 1);
        }
    }
}

/**
 * @param $string
 * @return string
 */
function slugify($string)
{
    $string = htmlentities(mb_convert_encoding($string, 'UTF-8', 'ASCII'), ENT_SUBSTITUTE, "UTF-8");

    $pattern = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
    $string = preg_replace($pattern, '$1', $string);

    $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

    $pattern = '~[^0-9a-z]+~i';
    $string = preg_replace($pattern, '-', $string);

    return strtolower(trim($string, '-'));}

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
//        usleep(400000);
    }
//    usleep(400000);
    foreach ($competition->getEvents() as $event) {
        print_r(PHP_EOL);
        print_r($GLOBALS['config']['parser']['disciplines'][$event->getId()][0] . " " . $event->getGenderName() . PHP_EOL);
//        usleep(400000);
        foreach ($event->getResults() as $result) {
            if(is_null($result)) var_dump($result);
            print_r($result->getYearOfBirth() . " " . $result->getFirstName() . " " . $result->getLastName() . " " . $result->getFirstTime() . PHP_EOL);
//            usleep(200000);
        }
    }
}
