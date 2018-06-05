<?php

namespace Lujo5\Lumen\Rest;

class RestRoute {

    /**
     * Create REST route for specified router object. Also, only specific route methods can be specified using last argument.
     *
     * @param $router \Laravel\Lumen\Routing\Router Router on which this REST route will be applied.
     * @param $prefix string Route prefix (e.g. users)
     * @param $controller string Full controller name string, controller must extend {@link \Lujo5\Lumen\Rest\RestController}
     * @param null $include array Provide an array of route methods to generate for this controller
     * (options: [INDEX, ONE, CREATE, UPDATE, DELETE]). If you want all the methods, ignore this argument or provide null vlaue.
     */
    public static function route($router, $prefix, $controller, $include = null) {
        if ($include != null) {
            if (is_array($include)) {
                $include = array_map('strtoupper', $include);
            } else {
                $include = array(strtoupper($include));
            }
        }
        if (self::isIncluded('INDEX', $include)) {
            $router->get($prefix, $controller . '@index');
        }
        if (self::isIncluded('ONE', $include)) {
            $router->get($prefix . '/{id}', $controller . '@one');
        }
        if (self::isIncluded('CREATE', $include)) {
            $router->post($prefix, $controller . '@create');
        }
        if (self::isIncluded('UPDATE', $include)) {
            $router->put($prefix . '/{id}', $controller . '@update');
        }
        if (self::isIncluded('DELETE', $include)) {
            $router->delete($prefix . '/{id}', $controller . '@delete');
        }
    }

    private static function isIncluded($method, $include) {
        return $include == null || in_array($method, $include);
    }
}