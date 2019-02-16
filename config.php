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
        'filename' => 'worldgames2017fix.pdf',
        'filetype' => 'pdf',
        'name' => 'World Games 2017',
        'date' => '2019-01-12', // yyyy-mm-dd
        'location' => 'Wroclaw, Poland',
        'clocktype' => 0, // 0 = unknown, 1 = electronic, 2 = hadndclocked
        'type' => 'WorldGames2017', // options: Splash, German, Spanish, French
        'line_conversion' => 'yes', // options: competition specific, see competition class convertLines()
    ],
    'parser' => [
        'splash' => [
            'event_signifiers' => ['Programmanr', 'Event'],
            'event_designifiers' => ['DISKWALIFICATIE CODES'], // signifies a line is definitely not an event line
            'result_rejectors' => ['DSQ', 'disq', 'DNS', 'DC 20', 'DC 1', 'Selectietijd', 'Splash Meet Manager', 'DNF', 'BR CAD', 'BR OPEN', 'BR JUN', 'BR M', 'BR BEN', 'BR MIN'],
            'event_rejectors' => ['Jongens', 'Meisjes'],
            'parse_yob' => 1,
            'disciplines' => [
                1 => ["100m manikin carry with fins", "100m popduiken met zwemvliezen", "100 m. remolque de maniquí", "100m manikin (ring) carry with fins", "100 m Manikin Carry with Fins", "100m mannequin palmes", "100 manikin carry with fins", "A2-Popredden met vinnen"],
                2 => ["50m manikin carry", "50m popduiken", "50 m. remolque de maniquí", "50m Mannequin", "50 manikin carry", "A4-Popredden"],
                3 => ["200m obstacle swim", "200m hinderniszwemmen", "200 m. natación con obstáculos", "200 m Obstacle Swim", "200m Obstacles", "A1-Hinderniszwemmen"],
                4 => ["100m manikin tow with fins", "100m lifesaver", "100 m. socorrista", "100 m Manikin Tow with Fins", "100 manikin tow with fins", "100m Manikin Tow with Fins", "A5-Lifesaver"],
                5 => ["100m rescue medley", "100m reddingswisselslag", "100 m. combinada de salvamento", "100 m Rescue Medley", "100m Combiné", "A3-Reddingscombiné"],
                6 => ["200m superlifesaver", "200 m. supersocorrista", "200m super lifesaver", "200 m Super Lifesaver", "A6-Super Lifesaver"],
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
                'male_signifiers' => ['Men5', 'Heren'],
                'female_signifiers' => ['Women5', 'Dames']
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
            ],
            'genders' => [
                'male_signifiers' => ['Men'],
                'female_signifiers' => ['Women']
            ],
            'parse_yob' => 1,
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
                'male_signifiers' => 'Messieurs',
                'female_signifiers' => 'Dames'
            ]
        ],
        'italian' => [
            'event_signifiers' => ['Donne', 'Uomini'],
            'event_designifiers' => ['Es.', 'Ragazzi'], // signifies a line is definitely not an event line
            'result_rejectors' => ['F/DQ'],
            'disciplines' => [
                1 => ["100m Manikin Carry Fins", "100m Manichino pinne - "],
                2 => ["50m Manikin Carry", "50m Trasporto manichino"],
                3 => ["200m Obstacle Swim", "200m Nuoto con ostacoli"],
                4 => ["100m Manikin Tow Fins", "100m Manich pinne torpedo"],
                5 => ["100m Rescue Medley", "100m Percorso misto"],
                6 => ["200m Super Lifesaver", "200m Super Lifesaver"],
                7 => ["50m Nuoto con ostacoli"],
                8 => ["50 m free style"],
                9 => ["50 m freestyle with fins", "50m Pinne"],
                10 => ["50m Trasp manichino acqua"],
                11 => ["50 m slepen"],
                12 => ["25 m pop"],
                13 => ["50 m vrij met torpedo"],
                14 => ["50m Trasp man pinne acqua"],
            ],
            'genders' => [
                'male_signifiers' => ['Uomini'],
                'female_signifiers' => ['Donne']
            ]
        ],
        'hytek' => [
            'event_signifiers' => ['Event'],
            'event_designifiers' => ['Events'], // signifies a line is definitely not an event line
            'event_rejectors' => ['Under 14', 'Relay'], // rejects current event, results below this are not included
            'result_rejectors' => ['SA REC', 'National:', 'APLSC:', 'WORLD:', 'Euro:', 'World: ', 'Australian:', '10:00.00', '5:00.00', 'DQ'],
            'parse_yob' => 0,
            'disciplines' => [
                1 => ["100 LC Metre Fins Manikin Carry", "100 LC Meter Manikin Rescue", "100 LC Meter Manikin Carr", "100 LC Meter manikin carry", "100 LC Meter Manikin Carry", "Manikin Carry w/Fins"],
                2 => ["50 LC Metre Manikin Carry", "50 LC Meter Manikin Rescue", "50 LC Meter Manikin Carr", "50 LC Meter mankin carry"],
                3 => ["200 LC Metre Obstacle", "200 LC Metre Masters Obstacle", "200 LC Meter Obstacle", "200 LC Meter Obstacles", "200 LC Meter 0bstacles"],
                4 => ["100 LC Metre Fins Manikin Tow", "100 LC Meter Manikin Tow", "100 LC Meter Manikin Tow", "100 LC Meter Manikin Tow"],
                5 => ["100 LC Metre Rescue Medley", "100 LC Meter Rescue Medley", "100 LC Meter Rescue Medle", "Rescue Medley"],
                6 => ["200 LC Metre Super Lifesaver", "200 LC Meter Super Lifesaver", "200 LC Meter Su", "uper Lifesaver"],
                7 => ["50m Nuoto con ostac45oli"],
                8 => ["50 m freeffff style"],
                10 => ["50 m mafffnikin"],
                11 => ["50 m slefffpen"],
                12 => ["25 m pfffop"],
                13 => ["50 m vrifffj met torpedo"],
                14 => ["50m Manichino455 pinne"],
            ],
            'genders' => [
                'male_signifiers' => ['Men', 'Boys'],
                'female_signifiers' => ['Women', 'Girls']
            ]
        ],
        'jauswertung' => [
            'event_signifiers' => ['Ergebnisse'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => [],
            'parse_yob' => 0,
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
                'male_signifiers' => ['männlich'],
                'female_signifiers' => ['weiblich']
            ]
        ],
        'rescue2014' => [
            'event_signifiers' => ['Heat', 'Final'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => ['00:00,00'],
            'parse_yob' => 0,
            'disciplines' => [
                1 => ["100 Manikin Carry with Fins"],
                2 => ["50 Manikin Carry"],
                3 => ["200 Obstacle Swim"],
                4 => ["100 Manikin tow with fins"],
                5 => ["100 Rescue medley"],
                6 => ["200 SLS"],
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
        ]
            ,'worldgames2017' => [
            'event_signifiers' => ['Ergebnisse'],
            'event_designifiers' => [], // signifies a line is definitely not an event line
            'event_rejectors' => [], // rejects current event, results below this are not included
            'result_rejectors' => [],
            'parse_yob' => 0,
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
                'male_signifiers' => ['männlich'],
                'female_signifiers' => ['weiblich']
            ]
        ],
    ]
);