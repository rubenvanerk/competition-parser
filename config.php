<?php

return array(
    'pdf_folder' => 'competitions/',
    'database' => [
        'host' => getenv('DB_PARSER_HOST'),
        'name' => getenv('DB_PARSER_NAME'),
        'username' => getenv('DB_PARSER_USERNAME'),
        'password' => getenv('DB_PARSER_PASSWORD'),
        'port' => getenv('DB_PARSER_PORT'),
    ],
    'competition' => [
        'filename' => 'ILSE Dordrecht 2018 2018-03-10 (1).pdf',
        'filetype' => 'pdf',
        'name' => 'World Games 2017',
        'date' => '2019-01-12', // yyyy-mm-dd
        'location' => 'Wroclaw, Poland',
        'clocktype' => 0, // 0 = unknown, 1 = electronic, 2 = hadndclocked
        'type' => 'Splash', // options: see classes/parsers
        'line_conversion' => '', // options: competition specific, see competition class convertLines()
    ],
    'parser' => [
        'template' => [
            'event_signifiers' => [],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => [],
            'parse_yob' => 1,
            'disciplines' => [
                1 => ["100m Manikin Carry with Fins"],
                2 => ["50m Manikin Carry"],
                3 => ["200m Obstacle Swim"],
                4 => ["100m Manikin Tow with Fins"],
                5 => ["100m Rescue Medley"],
                6 => ["200m Super Lifesaver"],
                7 => ["50m Obstacle Swim"],
                9 => ["50m Freestyle with Fins"],
                10 => ["50m Manikin Carry (relay leg 3)"],
                12 => ["25m Manikin Carry"],
                14 => ["50m Manikin Carry with Fins (relay leg 4)"],
            ],
            'genders' => [
                'male_signifiers' => ['Men'],
                'female_signifiers' => ['Women']
            ]
        ],
    ]
);