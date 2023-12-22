<?php

$router->get('api/products/all', 'ApiProductController@getAllProducts');
$router->get('api/product', 'ApiProductController@getSingleProduct,id');

$router->get('api/sales-orders/all', 'ApiSalesOrderController@getAllSalesOrders');
$router->get('api/sales-order', 'ApiSalesOrderController@getSingleSalesOrder,id');

$router->get('api/sales-orders/all-co2', 'ApiSalesOrderController@getAllCo2');
$router->get('api/sales-orders/date-interval-co2', 'ApiSalesOrderController@getIntervalCo2,start,end');

$router->get('api/sales-orders/destination-co2', 'ApiSalesOrderController@getDestinationCo2,country');
$router->get('api/sales-orders/product-co2', 'ApiSalesOrderController@getProductCo2,product');

$router->post('api/products', 'ApiProductController@insertProduct');
$router->post('api/sales-orders', 'ApiSalesOrderController@insertSalesOrders');
$router->post('api/sales-orders/sale', 'ApiSalesOrderController@insertProductInOrder,order');

$router->put('api/products/product', 'ApiProductController@updateProduct,id');
$router->put('api/sales/sale', 'ApiSalesOrderController@updateSales,id');
$router->put('api/sales-orders/order', 'ApiSalesOrderController@updateSalesOrders,id');
$router->put('api/sales-orders/sale', 'ApiSalesOrderController@updateProductInSalesOrders');

$router->delete('api/products/product', 'ApiProductController@deleteProduct');
$router->delete('api/sales/sale', 'ApiSalesController@deleteSales');
$router->delete('api/sales-orders/sale', 'ApiSalesOrderController@deleteProductInSalesOrders');
