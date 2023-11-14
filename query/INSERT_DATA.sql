/*---------------------------------PRODUCTS---------------------------------*/
INSERT INTO
    `products` (
        `product_code`,
        `name`,
        `saved_kg_co2`
    )
VALUES(
    "0100",
    "pork meat",
    5
);
INSERT INTO
    `products` (
        `product_code`,
        `name`,
        `saved_kg_co2`
    )
VALUES(
    "0234",
    "chicken breast",
    4
);
INSERT INTO
    `products` (
        `product_code`,
        `name`,
        `saved_kg_co2`
    )
VALUES(
    "1345",
    "calf meat",
    10
);
INSERT INTO
    `products` (
        `product_code`,
        `name`,
        `saved_kg_co2`
    )
VALUES(
    "6476",
    "hamburger",
    11
);
INSERT INTO
    `products` (
        `product_code`,
        `name`,
        `saved_kg_co2`
    )
VALUES(
    "5520",
    "rabbit meat",
    2
);
INSERT INTO
    `products` (
        `product_code`,
        `name`,
        `saved_kg_co2`
    )
VALUES(
    "1023",
    "boar meat",
    7
);

/*--------------------------------SALES-ORDERS------------------------------*/

INSERT INTO
    `sales_orders` (
        `sales_code`,
        `sales_date`,
        `destination`,
        `product_id`
    )
VALUES(
    "AA1015",
    "2023-10-20 15:20:00",
    "China",
    "0100"
);

INSERT INTO
    `sales_orders` (
        `sales_code`,
        `sales_date`,
        `destination`,
        `product_id`
    )
VALUES(
    "AF2310",
    "2023-11-01 08:15:20",
    "Ireland",
    "0234"
);
INSERT INTO
    `sales_orders` (
        `sales_code`,
        `sales_date`,
        `destination`,
        `product_id`
    )
VALUES(
    "BF0071",
    "2023-11-05 16:54:28",
    "Italy",
    "1345"
);
INSERT INTO
    `sales_orders` (
        `sales_code`,
        `sales_date`,
        `destination`,
        `product_id`
    )
VALUES(
    "AA1015",
    "2023-10-20 15:20:00",
    "China",
    "6476"
);
INSERT INTO
    `sales_orders` (
        `sales_code`,
        `sales_date`,
        `destination`,
        `product_id`
    )
VALUES(
    "CB1156",
    "2023-11-06 21:36:00",
    "Romania",
    "0100"
);