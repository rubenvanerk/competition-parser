<?php

return array(
    'pdf_folder' => 'competitions/',
//    'database' => [
//        'host' => getenv('DB_PARSER_HOST'),
//        'name' => getenv('DB_PARSER_NAME'),
//        'username' => getenv('DB_PARSER_USERNAME'),
//        'password' => getenv('DB_PARSER_PASSWORD'),
//        'port' => getenv('DB_PARSER_PORT'),
//    ],
    'database' => [
        'host' => 'localhost',
        'name' => 'lifesaving_rankings',
        'username' => 'lifesaving_rankings_django',
        'password' => '5EydU!G3jwTuewDq',
        'port' => '5432',
    ],
    'competition' => [
        'filename' => 'Deutschland Pokal 2017.pdf',
        'name' => 'Deutchland Pokal 2017',
        'date' => '2017-12-02',
        'location' => 'Warendorf',
        'clocktype' => 1, // 0 = unknown, 1 = electronic, 2 = handclocked
        'type' => 'german', // options: splash, german
    ],
    'parser' => [
        'splash' => [
            'event_signifiers' => ['Programmanr', 'Event'],
            'event_designifiers' => ['DISKWALIFICATIE CODES'], // signifies a line is definitely not a event line
            'result_rejectors' => ['DSQ', 'disq', 'DNS', 'DC 20', 'DC 1', 'Selectietijd', 'Splash Meet Manager', 'DNF']
        ],
        'german' => [
            'event_signifiers' => ['over all heats'],
            'event_designifiers' => [], // signifies a line is definitely not a event line
            'result_rejectors' => ['WR:', 'd.n.s.', 'DC ']
        ],
        'genders' => [
            'male_signifiers' => ['boys', 'men', 'heren', 'messieurs', 'garçons', 'jongens'],
            'female_signifiers' => ['women', 'dames', 'filles', 'meisjes'],
        ],
        'disciplines' => [
            1 => ["100m manikin carry with fins", "100m popduiken met zwemvliezen", "100 m. remolque de maniquí", "100m manikin (ring) carry with fins", "100 m Manikin Carry with Fins", "100m mannequin palmes", "100 manikin carry with fins"],
            2 => ["50m manikin carry", "50m popduiken", "50 m. remolque de maniquí", "50m Mannequin", "50 manikin carry"],
            3 => ["200m obstacle swim", "200m hinderniszwemmen", "200 m. natación con obstáculos", "200 m Obstacle Swim", "200m Obstacles"],
            4 => ["100m manikin tow with fins", "100m lifesaver", "100 m. socorrista", "100 m Manikin Tow with Fins", "100 manikin tow with fins"],
            5 => ["100m rescue medley", "100m reddingswisselslag", "100 m. combinada de salvamento", "100 m Rescue Medley", "100m Combiné"],
            6 => ["200m superlifesaver", "200 m. supersocorrista", "200m super lifesaver", "200 m Super Lifesaver"],
            7 => ["50 m obstacle swim"],
            8 => ["50 m free style"],
            9 => ["50 m freestyle with fins"],
            10 => ["50 m manikin"],
            11 => ["50 m slepen"],
            12 => ["25 m pop"],
            13 => ["50 m vrij met torpedo"],
            14 => ["50 m pop met vliezen"],
        ]
    ]
);