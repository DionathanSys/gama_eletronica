<?php

return [
    'params' => [
        // IMPORTANTE: 1 - Produção / 2 - Homologação
        "token" => env('AMBIENTE_NFE') == '1' ? env('TOKEN_NFE_PRODUCAO') : env('TOKEN_NFE_HOMOLOGACAO'),
        "ambiente" => env('AMBIENTE_NFE'),
        "options" => [
            "debug" => false,
            "timeout" => 60,
            "port" => 443,
            "http_version" => CURL_HTTP_VERSION_NONE
        ]
    ],
    'serie' => [
        'nfe_retorno'       => env('AMBIENTE_NFE') == '1' ? 5     : 850,
        'nfe_estorno'       => env('AMBIENTE_NFE') == '1' ? 849   : 851,
        'nfe_remessa'       => env('AMBIENTE_NFE') == '1' ? 700   : 852,
        'nfe_retorno_demo'  => env('AMBIENTE_NFE') == '1' ? 701   : 853,
    ],
    'item' => [
        'origem' => [
            '0' => 'Nacional',
            '1' => 'Estrangeira - Importação direta',
            '2' => 'Estrangeira - Adquirida no mercado interno',
            '3' => 'Nacional com mais de 40% de conteúdo estrangeiro',
            '4' => 'Nacional produzida através de processos produtivos básicos',
            '5' => 'Nacional com menos de 40% de conteúdo estrangeiro',
            '6' => 'Estrangeira - Importação direta, sem produto nacional similar',
            '7' => 'Estrangeira - Adquirida no mercado interno, sem produto nacional similar',
            '8' => 'Nacional, mercadoria ou bem com Conteúdo de Importação superior a 70%',
        ],
    ],
    'cfop' => [
        'intraestadual' => [
            'nfe_retorno'       => 5916,
            'nfe_estorno'       => 1949,
            'nfe_remessa'       => 5915,
            'nfe_retorno_demo'  => 5913,
        ],
        'interestadual' => [
            'nfe_retorno'       => 6916,
            'nfe_estorno'       => 2949,
            'nfe_remessa'       => 6915,
            'nfe_retorno_demo'  => 6913,
        ],
    ],
    'icms' => [
        'situacao_tributaria' => [
            'nfe_remessa' => 400,
            'nfe_retorno' => 900,
    ],
    ],
    'pis' => [
        'situacao_tributaria' => '08',
    ],
    'cofins' => [
        'situacao_tributaria' => '08',
    ],
];
