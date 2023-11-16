<?php

return [
    "GET" => [
        "products" => [
                "all" => [
                    "query" => "",
                    "file" => "./product/read-all.php",
                ],
                "{id}" => [
                    "query" => "",
                    "file" => "./product/read-id.php",
                ]
        ],

        "sales-orders" => [
                "all" => [
                    "query" => "",
                    "file" => "./sales-order/read-all.php",
                ],
                "{id}" => [
                    "query" => "",
                    "file" => "./sales-order/read-id.php",
                ],
                "date-interval" => [
                    "query" => "start={value}&end={value}",
                    "file" => "./sales-order/",
                ],
                "destination" => [
                    "query" => "country={value}",
                    "file" => "./sales-order/",
                ]
        ]        
    ],
    "POST" => [
        "products" => [
            "query"=>"",
            "file"=>"./product/insert.php",
        ],
        "sales-orders" => [
            "query"=>"",
            "file"=>"./sales-order/insert.php",
        ]
    ],
    "PUT" => [
        "products" => [
            "query" => [
                "query"=>"code",
                "file"=>"./product/update.php"
            ]
        ],
        "sales-orders" => [
            "query" => [
                "query"=>"code",
                "file"=>"./sales-order/update.php"
            ]
        ]
    ],
    "DELETE" => [
        "products" => [
            "query" => [
                "query"=>"code",
                "file"=>"./product/delete.php"
            ]
        ],
        "sales-orders" => [
            "query" => [
                "query"=>"code",
                "file"=>"./sales-order/delete.php"
            ]
        ]
    ]
];

?>