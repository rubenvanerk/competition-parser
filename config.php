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
        'filename' => 'Open Spanish Nationals 2018 2018-05-05.pdf',
        'filetype' => 'pdf',
        'name' => 'Open Spanish National Championships 2018',
        'date' => '2018-05-05',
        'location' => 'Madrid',
        'clocktype' => 0, // 0 = unknown, 1 = electronic, 2 = handclocked
        'type' => 'Spanish', // options: Splash, German, Spanish, French
        'line_conversion' => 1, // options: competition specific, see competition class convertLines()
    ],
    'parser' => [
        'splash' => [
            'event_signifiers' => ['Programmanr', 'Event'],
            'event_designifiers' => ['DISKWALIFICATIE CODES'], // signifies a line is definitely not an event line
            'result_rejectors' => ['DSQ', 'disq', 'DNS', 'DC 20', 'DC 1', 'Selectietijd', 'Splash Meet Manager', 'DNF'],
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
        ],
        'german' => [
            'event_signifiers' => ['over all heats'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'result_rejectors' => ['WR:', 'd.n.s.', 'DC '],
            'disciplines' => [
                1 => ["100m manikin carry with fins", "100m popduiken met zwemvliezen", "100 m. remolque de maniquí", "100m manikin (ring) carry with fins", "100 m Manikin Carry with Fins", "100m mannequin palmes", "100 manikin carry with fins"],
                2 => ["50m manikin carry", "50m popduiken", "50 m. remolque de maniquí", "50m Mannequin", "50 manikin carry", "50 m manikin"],
                3 => ["200m obstacle swim", "200m hinderniszwemmen", "200 m. natación con obstáculos", "200 m Obstacle Swim", "200m Obstacles"],
                4 => ["100m manikin tow with fins", "100m lifesaver", "100 m. socorrista", "100 m Manikin Tow with Fins", "100 manikin tow with fins"],
                5 => ["100m rescue medley", "100m reddingswisselslag", "100 m. combinada de salvamento", "100 m Rescue Medley", "100m Combiné"],
                6 => ["200m superlifesaver", "200 m. supersocorrista", "200m super lifesaver", "200 m Super Lifesaver"],
                7 => ["50 m obstacle swim"],
                8 => ["50 m free style"],
                9 => ["50 m freestyle with fins"],
                10 => ["50 m manikin nope this is relay"],
                11 => ["50 m slepen"],
                12 => ["25 m pop"],
                13 => ["50 m vrij met torpedo"],
                14 => ["50 m pop met vliezen"],
            ]
        ],
        'spanish' => [
            'event_signifiers' => ['masculino', 'femenino', 'm.'],
            'event_designifiers' => ['Elim.T'], // signifies a line is definitely not an event line
            'result_rejectors' => ['00:00:00'],
            'disciplines' => [
                1 => ["100 m. remolque de maniquí"],
                2 => ["50 m. remolque de maniquí"],
                3 => ["200 m. natación con obstáculos"],
                4 => ["100 m. socorrista"],
                5 => ["100 m. combinada de salvamento"],
                6 => ["200 m. supersocorrista", "200 m. súper socorrista"],
                7 => ["50 m obstacle swim"],
                8 => ["50 m free style"],
                9 => ["50 m freestyle with fins"],
                10 => ["50 m manikin"],
                11 => ["50 m slepen"],
                12 => ["25 m pop"],
                13 => ["50 m vrij met torpedo"],
                14 => ["50 m pop met vliezen"],
            ],
            'genders' => [
                'male_signifiers' => ['masculino', 'M'],
                'female_signifiers' => ['femenino', 'F']
            ]
        ],
        'french' => [
            'event_signifiers' => ['Dames', 'Messieurs'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'result_rejectors' => ['F/DQ'],
            'disciplines' => [
                1 => ["100 m Man. Palmes"],
                2 => ["50 m Man."],
                3 => ["200 m Obstacles"],
                4 => ["100 m Bouée Tube"],
                5 => ["100 m Combiné"],
                6 => ["200 m SLS"],
                7 => ["50 m obstacle swim nope"],
                8 => ["50 m free style nope"],
                9 => ["50 m freestyle with nope"],
                10 => ["50 m nope"],
                11 => ["50 m nope"],
                12 => ["25 m nope"],
                13 => ["50 m nope met torpedo"],
                14 => ["50 m nope met vliezen"],
            ],
            'genders' => [
                'male_signifiers' => 'Dames',
                'female_signifiers' => 'Messieurs'
            ]
        ]
    ]
);