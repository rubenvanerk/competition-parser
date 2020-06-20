# DEPRECATED

This project is superseded by [competition-parser-lumen](https://github.com/rubenvanerk/competition-parser-lumen).

# competition-parser
A parser to convert pdf to results for Lifesaving Rankings

Dependency for [Lifesaving Rankings](https://github.com/rubenvanerk/lifesavingrankings)

## Project overview
The program starts in `application.php`. This file is executed from the command line and takes 1 required argument: a configuration file. This configuration file contains info like competition name, date, name format and time format.  
The first step in parsing the results is converting the file to lines of text. The script then loops through each line detecting what kind of information the lines stores. This could be an event, a time or something else.

## Setup

### Prerequisites
Both php7 and composer need to be installed.

### Steps
1. Use `composer install` to install dependencies  
2. You'll need a local database. The only way to do this is to setup the main project [Lifesaving Rankings](https://github.com/rubenvanerk/lifesavingrankings)  
3. Copy the `.env.example` to `.env` and edit its contents to reflect your environment  
4. To execute the program, use `php application.php competitions/example.yaml`  

## Configuration
Because every results file is set up differently, every competition needs its own configuration file. There are some required values in these files and values from the `config.yaml` file in the project root can be overwritten in competition config files.

### file
This is the filename of the results. The script will search for this file in the `competitions` directory.

### name
The competition name.

### date
The competition date. Format: YYYY-MM-DD. If a competition is on multiple days, use the first day of the competition.

### location
The location of the competition. If the location is unknown use Unknown in this field.

### clock_type
This value is not used on LifesavingRankings.com but is required in the script. Use `1` for electronic timekeeping, `2` for hand clocked times and `0` if the type of timekeeping is unknown.

### parser_config
The parser config contains all information about where and how data is can be found in the file

#### line_conversion
Sometimes lines are not readable after converting a pdf to text. Use one of the following options to convert the lines.  

| Value | Effect |
|--------------------------|--------------------------------------------------------------------------------------------------------------------------------|
| name-yob-club-time | When these 4 values are displayed on separate lines, this line conversion will move them to one line. |
| name-yobclub-time | When these 3 values are displayed on separate lines, this line conversion will move them to one line. |
| name-time | When these 2 values are displayed on separate lines, this line conversion will move them to one line. |
| time-name | When these 2 values are displayed on separate lines, this line conversion will move them to one line. |
| club-time-name | When these 3 values are displayed on separate lines, this line conversion will move them to one line. |
| striptags | Strips tags. |
| remove-nicknames | Removes all text between parentheses `(example)`. Very specific to one competition |
| w-space | In HY-TEK results it is common that spaces are inserted after a W when converting to text. Use this value to remove the space. |
| name-yob-club-time-time | When these 5 values are displayed on separate lines, this line conversion will move them to one line. |
| jauswertung | In Jauswertung competition, event names may be displayed below results.  This moves the event name above the results |
| move-events-spain-to-top | Same as `jauswertung` but for Spanish results. Not very stable. |

#### time_index
This value determines which times included in the database. Possible options are: `first`, `last`, `all` or an integer that is used as index.

#### separate_gender
In most results, gender and event names are on the same line. If that's not the case, set this value to 1.

#### formats
`formats` describes in what formats names, times and year of births can be found. For possible regexes, see `config.yaml` in the root of the project.

#### rounds
If a competition has multiple rounds (eg. finals) use this value to specify what the rounds are called and what weight they get. The higher the round number, the more important the round. A finals should get the highest round number.
