<?php

$router->get('api/products/all', 'ApiProductController@getAllProducts');
$router->get('api/product', 'ApiProductController@getSingleProduct');

$router->get('api/sales-orders/all', 'ApiSalesOrderController@getAllSalesOrders');
$router->get('api/sales-order', 'ApiSalesOrderController@getSingleSalesOrder');

$router->get('api/sales-orders/all-co2', 'ApiSalesOrderController@getAllCo2');
$router->get('api/sales-orders/date-interval-co2', 'ApiSalesOrderController@getIntervalCo2');

$router->get('api/sales-orders/destination-co2', 'ApiSalesOrderController@getDestinationCo2');
$router->get('api/sales-orders/product-co2', 'ApiSalesOrderController@getProductCo2');

$router->post('api/products', 'ApiProductController@insertProduct');
$router->post('api/sales-orders', 'ApiSalesOrderController@insertSalesOrders');
$router->post('api/sales-orders/sale', 'ApiSalesOrderController@insertProductInOrder');

$router->put('api/products/product', 'ApiProductController@updateProduct');
$router->put('api/sales/sale', 'ApiSalesController@updateSales');
$router->put('api/sales-orders/sale', 'ApiSalesOrderController@updateProductInSalesOrders');
$router->put('api/sales-orders/order', 'ApiSalesOrderController@updateSalesOrders');

$router->delete('api/products/product', 'ApiProductController@deleteProduct');
$router->delete('api/sales/sale', 'ApiSalesController@deleteSales');
$router->delete('api/sales-orders/sale', 'ApiSalesOrderController@deleteProductInSalesOrders');
