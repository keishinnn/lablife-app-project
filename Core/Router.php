<?php

// file path = root/Core/Router.php

namespace Core;

class Router
{
    protected $routes = [];

    public function add($method, $uri, $controller)
    {
        $this->routes[] = [
            'uri' => $uri,
            'controller' => $controller,
            'method' => $method
        ];
    }

    public function get($uri, $controller)
    {
        $this->add('GET', $uri, $controller);
    }

    public function post($uri, $controller)
    {
        $this->add('POST', $uri, $controller);
    }

    public function delete($uri, $controller)
    {
        $this->add('DELETE', $uri, $controller);
    }

    public function patch($uri, $controller)
    {
        $this->add('PATCH', $uri, $controller);
    }

    public function put($uri, $controller)
    {
        $this->add('PUT', $uri, $controller);
    }

    public function route($uri, $method)
    {
        foreach ($this->routes as $route) {
            if ($route['uri'] == $uri && $route['method'] == strtoupper($method)) {

                $controller = $route['controller'];

                if (strpos($controller, '@') !== false) {
                    [$class, $method] = explode('@', $controller);

                    $class = "Controllers\\" . $class;
                    if (class_exists($class)) {
                        $instance = new $class();
                        return call_user_func([$instance, $method]);
                    }

                    throw new \Exception("Controller class $class not found.");
                }
                return require base_path($controller);
            }
        }

        $this->abort();
    }


    function abort()
    {
        header("Location: /");
        exit;
    }
}
