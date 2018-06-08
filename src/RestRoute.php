<?php

namespace Lujo\Lumen\Rest;

class RestRoute {

    /**
     * Create REST route for specified router object. Also, only specific route methods can be specified using last argument.
     *
     * @param $router \Laravel\Lumen\Routing\Router Router on which this REST route will be applied.
     * @param $prefix string Route prefix (e.g. users)
     * @param $controller string Full controller name string, controller must extend {@link \Lujo\Lumen\Rest\RestController}
     * @param $include array Provide an array of route functions to generate for this controller
     * (options: [INDEX, ONE, CREATE, UPDATE, DELETE]). If you want all the methods, ignore this argument or provide null vlaue.
     * @param $middlewares array An array of middleware keys to be used on specific or all functions on this route.
     * e.g.1 apply on all functions: ['auth', 'example', 'check'], e.g.2 apply specific middleware on specific function:
     * ['INDEX' => ['check'], 'CREATE' => ['auth', 'check'], 'ONE' => ['auth']]. All middlewares used here must be
     * registered using $app->routeMiddleware(...)
     */
    public static function route($router, $prefix, $controller, $include = null, $middlewares = []) {
        if ($include != null) {
            if (is_array($include)) {
                $include = array_map('strtoupper', $include);
            } else {
                $include = array(strtoupper($include));
            }
        }
        if (self::isIncluded('INDEX', $include)) {
            $router->get($prefix, $controller . '@index')->middleware(self::resolveMiddlewares('INDEX', $middlewares));
        }
        if (self::isIncluded('ONE', $include)) {
            $router->get($prefix . '/{id}', $controller . '@one')->middleware(self::resolveMiddlewares('ONE', $middlewares));
        }
        if (self::isIncluded('CREATE', $include)) {
            $router->post($prefix, $controller . '@create')->middleware(self::resolveMiddlewares('CREATE', $middlewares));
        }
        if (self::isIncluded('UPDATE', $include)) {
            $router->put($prefix . '/{id}', $controller . '@update')->middleware(self::resolveMiddlewares('UPDATE', $middlewares));
        }
        if (self::isIncluded('DELETE', $include)) {
            $router->delete($prefix . '/{id}', $controller . '@delete')->middleware(self::resolveMiddlewares('DELETE', $middlewares));
        }
    }

    private static function isIncluded($functionName, $include) {
        return $include == null || in_array($functionName, $include);
    }

    private static function resolveMiddlewares($functionName, $middlewares) {
        if ($middlewares == null || empty($middlewares)) {
            return [];
        }
        if (is_string($middlewares)) {
            return $middlewares;
        }
        if (self::isAssociativeArray($middlewares)) {
            try {
                $specificMiddlewares = $middlewares[$functionName];
            } catch (\ErrorException $e) {
                return [];
            }
            if (is_string($specificMiddlewares)) {
                return $specificMiddlewares;
            }
            return empty($specificMiddlewares) ? [] : $specificMiddlewares;
        } else {
            return $middlewares;
        }
    }

    private static function isAssociativeArray(array $arr) {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}