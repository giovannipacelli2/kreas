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
                "all-co2" => [
                    "file" => "./sales-order/query/all-co2.php",
                ],
                "{id}" => [
                    "file" => "./sales-order/crud/read-id.php",
                ],
                "date-interval-co2" => [
                    "query" => ["start", "end"],
                    "file" => "./sales-order/query/date-interval.php",
                ],
                "destination-co2" => [
                    "query" => ["country"],
                    "file" => "./sales-order/query/destination.php",
                ],
                "product-co2" => [
                    "query" => ["product"],
                    "file" => "./sales-order/query/product-id.php",
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
                "query"=>["code"],
                "file"=>"./product/crud/update.php"
            ]
        ],
        "sales-orders" => [
            "query" => [
                "query"=>["code"],
                "file"=>"./sales-order/crud/update.php"
            ]
        ]
    ],
    "DELETE" => [
        "products" => [
            "query" => [
                "query"=>["code"],
                "file"=>"./product/crud/delete.php"
            ]
        ],
        "sales-orders" => [
            "query" => [
                "query"=>["code"],
                "file"=>"./sales-order/crud/delete.php"
            ]
        ]
    ]
];

?>