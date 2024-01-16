<?php

namespace App\core;

/*
    'GET' => [
        'api/product' => 'ApiProductController@getSingleProduct'
    ]
*/

class Router
{
    public $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public static function load($file)
    {
        $router = new static();

        require $file;

        return $router;
    }

    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function put($uri, $controller)
    {
        $this->routes['PUT'][$uri] = $controller;
    }

    public function delete($uri, $controller)
    {
        $this->routes['DELETE'][$uri] = $controller;
    }

    public function direct($uri, $requestType)
    {
        if (array_key_exists($uri, $this->routes[$requestType])) {
            return $this->callAction(
                ...explode('@', $this->routes[$requestType][$uri])
            );
        } else {

            $tmp = explode('/', $uri);
            $last = count($tmp) - 1;

            $res = '';
            for ($i = 0; $i < $last; $i++) {
                $res = $res . $tmp[$i] . '/';
            }

            $uri = $res . '{id}';

            if (array_key_exists($uri, $this->routes[$requestType])) {

                $ctrl = explode('@', $this->routes[$requestType][$uri]);
                $id = $tmp[$last];

                array_push($ctrl, $id);

                return $this->callAction(...$ctrl);
            }
        }

        throw new \RuntimeException('No route defined for this URI.');
    }

    protected function callAction($controller_name, $action, $id = '')
    {

        $match_params = preg_match('/(,)/', $action);

        if ($match_params) {

            $action = explode(',', $action);
            $params = [];
            for ($i = 1; $i < count($action); $i++) {
                array_push($params, $action[$i]);
            }
            $action = $action[0];

        }

        if ($id !== '') {

            $params = [
                $params[0] => $id,
            ];

        }

        $controller = "App\\controllers\\{$controller_name}";
        $controller = new $controller();

        if (!method_exists($controller, $action)) {

            throw new \RuntimeException(
                "{$controller_name} doesn't respond to the {$action} action"
            );
        }

        if (isset($params)) {
            //return $controller->$action($params);
            return call_user_func_array([$controller, $action], [$params]);
        }

        return call_user_func([$controller, $action]);
    }
}
