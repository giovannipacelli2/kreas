<?php

return [
    "GET" => [
        "products" => [
                "all" => [
                    "file" => "./product/crud/read-all.php",
                ],
                "{id}" => [
                    "file" => "./product/crud/read-id.php",
                ]
        ],

        "sales-orders" => [
                "all" => [
                    "file" => "./sales-order/crud/read-all.php",
                ],
                "{id}" => [
                    "file" => "./sales-order/crud/read-id.php",
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
            "file"=>"./product/crud/insert.php",
        ],
        "sales-orders" => [
            "file"=>"./sales-order/crud/insert.php",
        ]
    ],
    "PUT" => [
        "products" => [
            "query" => [
                "query"=>"code",
                "file"=>"./product/crud/update.php"
            ]
        ],
        "sales-orders" => [
            "query" => [
                "query"=>"code",
                "file"=>"./sales-order/crud/update.php"
            ]
        ]
    ],
    "DELETE" => [
        "products" => [
            "query" => [
                "query"=>"code",
                "file"=>"./product/crud/delete.php"
            ]
        ],
        "sales-orders" => [
            "query" => [
                "query"=>"code",
                "file"=>"./sales-order/crud/delete.php"
            ]
        ]
    ]
];

?>