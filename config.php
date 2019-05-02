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
//    'database' => [
//        'host' => getenv('DB_PARSER_HOST'),
//        'name' => 'finswimming_rankings',
//        'username' => 'finswimming_rankings',
//        'password' => getenv('DB_PASS_FINSWIMMING'),
//        'port' => 5432,
//    ],
    'competition' => [
        'filename' => '2019-misp.pdf',
        'filetype' => 'pdf',
        'name' => 'MISP 2019',
        'date' => '2019-04-28', // yyyy-mm-dd
        'location' => 'Seraing, Belgium',
        'clocktype' => 1, // 0 = unknown, 1 = electronic, 2 = handclocked
        'type' => 'Splash', // options: see classes/parsers
        'line_conversion' => '', // options: competition specific, see competition class convertLines()
        'pool_length' => 0, // set to falsy if not supported
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
                18 => ["100m obs"],
            ],
            'genders' => [
                'male_signifiers' => ['Men'],
                'female_signifiers' => ['Women']
            ]
        ],
        'finswimming' => [
            'event_signifiers' => [],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => [],
            'parse_yob' => 1,
            'disciplines' => [
                1 => ["50m Apnoea"],
                2 => ["50m BiFins"],
                3 => ["100m BiFins"],
                4 => ["200m BiFins"],
                5 => ["400m BiFins"],
                6 => ["50m Surface"],
                7 => ["100m Surface"],
                8 => ["200m Surface"],
                9 => ["400m Surface"],
                10 => ["800m Surface"],
                11 => ["1500m Surface"],
                12 => ["400m Immersion"],
                13 => ["800m perslucht"],
                14 => ["25m afstand duiken"],
                15 => ["25m met vinnen"],
                16 => ["100m perslucht"],
                17 => ["200m perslucht"],
            ],
            'genders' => [
                'male_signifiers' => ['Men'],
                'female_signifiers' => ['Women']
            ]
        ],
    ]
);