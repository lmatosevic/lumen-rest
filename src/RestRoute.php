<?php

namespace Lujo\Lumen\Rest;

class RestRoute {

    /**
     * Create REST route for specified router object. Also, only specific route methods can be specified using last argument.
     *
     * @param $router \Laravel\Lumen\Routing\Router Router on which this REST route will be applied.
     * @param $prefix string Route prefix (e.g. users)
     * @param $controller string Full controller name string, controller must extend {@link \Lujo\Lumen\Rest\RestController}
     * @param $middlewares array|string An array of middleware (or one middleware) keys to be used on specific or all
     * functions on this route. e.g.1 Apply on all functions: ['first', 'second', 'third'],
     * e.g.2 Apply specific middleware on specific function: ['INDEX' => 'first', 'CREATE' => ['first', 'second'], 'ONE' => ['first']].
     * e.g.3 Also, it is possible to combine previous two examples ('third' will be applied to 'CREATE', 'UPDATE', 'DELETE'
     * (all except 'ONE' and 'INDEX'): ['ONE' => 'first', 'INDEX' => ['first', 'second'], 'third'].
     * e.g.4 In addition, function names can be combined using comma delimiter: ['INDEX,ONE' => ['first', 'second'], 'third']
     * All middlewares used here must be registered using $app->routeMiddleware(...)
     * @param $include array|string Provide an array of route functions to generate for this controller
     * (options: [INDEX, ONE, CREATE, UPDATE, DELETE]). If you want all the methods, ignore this argument or provide null vlaue.
     */
    public static function route($router, $prefix, $controller, $middlewares = [], $include = null) {
        if ($include !== null) {
            if (is_array($include)) {
                $include = array_map('strtoupper', $include);
            } else {
                $include = array(strtoupper($include));
            }
        }
        if (self::isIncluded('INDEX', $include)) {
            $router->get($prefix, [
                'middleware' => self::resolveMiddlewares('INDEX', $middlewares),
                'uses' => $controller . '@index'
            ]);
        }
        if (self::isIncluded('ONE', $include)) {
            $router->get($prefix . '/{id}', [
                'middleware' => self::resolveMiddlewares('ONE', $middlewares),
                'uses' => $controller . '@one'
            ]);
        }
        if (self::isIncluded('CREATE', $include)) {
            $router->post($prefix, [
                'middleware' => self::resolveMiddlewares('CREATE', $middlewares),
                'uses' => $controller . '@create'
            ]);
        }
        if (self::isIncluded('UPDATE', $include)) {
            $router->put($prefix . '/{id}', [
                'middleware' => self::resolveMiddlewares('UPDATE', $middlewares),
                'uses' => $controller . '@update'
            ]);
        }
        if (self::isIncluded('DELETE', $include)) {
            $router->delete($prefix . '/{id}', [
                'middleware' => self::resolveMiddlewares('DELETE', $middlewares),
                'uses' => $controller . '@delete'
            ]);
        }
    }

    private static function isIncluded($functionName, $include) {
        return $include === null || in_array($functionName, $include);
    }

    private static function resolveMiddlewares($functionName, $middlewares) {
        if ($middlewares === null || empty($middlewares)) {
            return [];
        }
        if (is_string($middlewares)) {
            return $middlewares;
        }
        $resolved = self::getAssociativeValues($middlewares, $functionName);
        if ($resolved === null) {
            $resolved = self::getNonAssociativeValues($middlewares);
        }
        return $resolved;
    }

    private static function getNonAssociativeValues($array) {
        $values = [];
        for ($i = 0; $i < count($array); $i++) {
            try {
                $value = $array[$i];
                array_push($values, $value);
            } catch (\ErrorException $e) {
                continue;
            }
        }
        return $values;
    }

    private static function getAssociativeValues($array, $key) {
        try {
            $middlewares = [];
            foreach (array_keys($array) as $k) {
                $functions = explode(',', $k);
                if ($functions === false) {
                    continue;
                }
                foreach ($functions as $fname) {
                    if (strtoupper($fname) === $key) {
                        $values = $array[$k];
                        if (is_string($values)) {
                            $values = [$values];
                        }
                        $middlewares = array_merge($middlewares, $values);
                    }
                }
            }
            $specificMiddlewares = array_unique($middlewares);
        } catch (\ErrorException $e) {
            return null;
        }
        return empty($specificMiddlewares) ? null : $specificMiddlewares;
    }

}