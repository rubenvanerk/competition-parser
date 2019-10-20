<?php namespace CompetitionParser\Classes\Models;

use Illuminate\Database\Eloquent\Model;

class Athlete extends Model
{
    protected $table = 'rankings_athlete';
    public $timestamps = false;

    public function nationalities()
    {
        return $this->belongsToMany('CompetitionParser\Classes\Models\Nationality', 'rankings_athlete_nationalities');
    }

    public static function findOrCreate($name, $gender, $yearOfBirth, $nation = null)
    {
//        print_r([$name, $gender, $yearOfBirth, $nation]);
        $athlete = Athlete::where('name', 'ilike', $name)
            ->where('gender', $gender);
        if(!is_null($yearOfBirth) && $yearOfBirth !== 'unknown') {
            $athlete->where('year_of_birth', $yearOfBirth);
        }

        $athlete = $athlete->first();

        if (!$athlete) {
            $slug = slugify($name);
            while(Athlete::where('slug', $slug)->exists()) {
                $lastChar = substr($slug, -1);
                if (is_numeric($lastChar)) {
                    $lastChar = intval($lastChar) + 1;
                    $slug = preg_replace('/-[0-9]+$/', '', $slug);
                } else {
                    $lastChar = 1;
                }
                $slug .= "-" . $lastChar;
            }

            $athlete = new Athlete();
            $athlete->name = $name;

            $athlete->gender = $gender;
            $athlete->slug = $slug;
            if ($yearOfBirth !== 'unknown') {
                $athlete->year_of_birth = $yearOfBirth;
            } else {
                $athlete->year_of_birth = null;
            }

            $athlete->save();
        }

        if (!$nation) {
            return $athlete;
        }

        $nationality = Nationality::where('lenex_code', $nation)->first();
        if (!$nationality) {
            print_r('Nationality with code ' . $nation . ' not found!');
            exit;
        }
        if (!$athlete->nationalities()->where('lenex_code', $nation)->first()) {
            $athlete->nationalities()->save($nationality);
            $athlete->save();
        }


        return $athlete;

    }
}