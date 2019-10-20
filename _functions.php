<?php

use CompetitionParser\Classes\Models\Competition;

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
function slugify($string) {

    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-', '.' => '-'
    );

    $stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $string);

    $slug = strtolower(strtr($stripped, $table));
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $slug);
    return $slug;


}
/**
 * @param $time
 * @return mixed|string
 * @throws Exception
 */
function toSqlInterval($time)
{
    $time = str_replace(',', '.', $time);
    $time = str_replace("'", ':', $time);
    $time = str_replace('"', '.', $time);
    $time = preg_replace('/(?<=\.[0-9]{2})0/', '', $time);

    if (strpos($time, '.') === false) {
        $time = substr_replace($time, '.', strrpos($time, ':'), strlen(':'));
    }


    if (strlen($time) == 4) {
        $time = '00:00:0' . $time;
    } elseif (strlen($time) == 5) {
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
 * @param $type
 */
function printCompetition($competition, $type)
{
    $disciplines = [
        1 => ["100m Manikin Carry with Fins"],
        2 => ["50m Manikin Carry"],
        3 => ["200m Obstacle Swim"],
        4 => ["100m Manikin Tow with Fins"],
        5 => ["100m Rescue Medley"],
        6 => ["200m Super Lifesaver"],
        7 => ["50m Obstacle Swim"],
        8 => ["50m freestyle"],
        9 => ["50m Freestyle with Fins"],
        10 => ["50m Manikin Carry (relay leg 3)"],
        11 => ["50m person tow"],
        12 => ["25m Manikin Carry"],
        13 => ["50m freestyle with tube"],
        14 => ["50m Manikin Carry with Fins (relay leg 4)"],
        18 => ["100m obs"],
    ];
    print_r($competition->name . PHP_EOL);
    print_r('Number of results: ' . $competition->countResults() . PHP_EOL);
    sleep(1);
    foreach ($competition->getEvents() as $event) {
        print_r($disciplines[$event->getId()][0] . " " . $event->getGenderName() . " round: " . $event->getRoundNumber() . PHP_EOL);
//        usleep(400000);
    }
    usleep(400000);
    foreach ($competition->getEvents() as $event) {
        print_r(PHP_EOL);
        print_r($disciplines[$event->getId()][0] . " " . $event->getGenderName() . " round: " . $event->getRoundNumber() . PHP_EOL);
        usleep(400000);
        foreach ($event->getResults() as $result) {
            print_r($result->getYearOfBirth() . " '" . $result->getName() . "' " . json_encode($result->getTimes()) . PHP_EOL);
//            usleep(200000);
        }
        if($event->countResults() > 0 && $event->getGender() == 0) sleep(5);
    }
}

function writeToFile($lines)
{
    file_put_contents("lines.txt", "");
    $file = fopen("lines.txt", "a");
    foreach ($lines as $line) {
        fwrite($file, $line . PHP_EOL);
    }
    fclose($file);
}


/**
 * @param $lines
 * @param \CompetitionParser\Classes\Helpers\CompetitionParser $competitionParser
 * @return mixed
 */
function moveEventsInJauswertung($lines, $competitionParser)
{
    $i = 0;
    foreach ($lines as $line) {
        if ($competitionParser->getEventIdFromLine($line) && $competitionParser->lineContains($lines[$i + 2], ['Ergebnisse'])) {
            for($j = $i; ; $j--) {
                if (substr($lines[$j], 0, 2) == '1 ') {
                    $lines[$j - 1] = $line . ' ' . $lines[$i + 2];
                    break;
                }
            }
        }
        $i++;
    }
    return $lines;
}
