<?php

namespace Lujo\Lumen\Rest;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Util {
    /**
     * Get paginate parameters from request query.
     *
     * skip - How many resources to skip (e.g. 30)
     * limit - How many resources to retreive (e.g. 15)
     * sort - Filed on which to sort returned resources (e.g. 'first_name')
     * order - Ordering of returend resources ('asc' or 'desc')
     *
     * @param Request $request request object received
     * @return array Array with skip, limit, sort and order
     */
    public static function paginateParams(Request $request) {
        $skip = $request->input('skip') ?? -1;
        $limit = $request->input('limit') ?? -1;
        $sort = $request->input('sort') ?? '';
        $order = $request->input('order') ?? 'asc';
        return array($skip, $limit, $sort, $order);
    }

    /**
     * Prepares query for execution using provided parameters. If request is null, then pagination and sort will be
     * skipped. Also, you can provide with array to specify which sub-models (relations) to include and where array
     *  to specify which entities to retrun.
     *
     * @param Request $request Request object used for querying and pagination on returning multiple entities.
     *  Can be null value.
     * @param Model $model Model object used as a reference for querying database.
     * @param array $with Array of relations to be included in returned model/models.
     * @param array $where Array of conditions on which to return models.
     * @param callable $queryFunction Pass query function for making additional complex queries in WHERE section
     *  e.g. function($query) { $query->where(...)->orWhere(...);} Leave blank or pass null if unused.
     * @return mixed Returns query builder object. Can be executed by calling get(), find() or other function.
     */
    public static function prepareQuery($request, $model, $with = [], $where = [], $queryFunction = null) {
        $query = $model;
        if (is_array($with) && count($with) > 0) {
            $query = $query->with($with);
        }
        if (is_array($where) && count($where) > 0) {
            $query = $query->where($where);
        }
        if ($queryFunction !== null && is_callable($queryFunction)) {
            $query->where($queryFunction);
        }
        if ($request === null) {
            return $query;
        }
        list($skip, $limit, $sort, $order) = self::paginateParams($request);
        $query = $query->skip($skip)->take($limit);
        $query = ($sort != '' && $order != '') ? $query->orderBy($sort, $order) : $query;
        return $query;
    }

    /**
     * Returns the successful JSON response.
     * Format of returned content is following:
     *
     * HTTP 200 OK
     * {
     *  "success": true,
     *  "data": {...} | [....] | "response text"
     * }
     *
     * @param $data mixed Data to return to the client.
     * @param array $headers Optional headers to return from server.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function successResponse($data, $headers = []) {
        return response()->json(['success' => true, 'data' => $data], 200, $headers);
    }

    /**
     * Returns the error JSON response with provided HTTP status code.
     * Format of returned content is following:
     *
     * HTTP {CODE} {ERROR_DESCRIPTION}
     * {
     *  "success": false,
     *  "data": {...} | [....] | "error text"
     * }
     * @param $data mixed Data to return to the client.
     * @param $code number HTTP error code.
     * @param array $headers Optional headers to return from server.
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorResponse($data, $code, $headers = []) {
        return response()->json(['success' => false, 'data' => $data], $code, $headers);
    }
}