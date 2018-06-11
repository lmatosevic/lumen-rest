<?php

namespace Lujo\Lumen\Rest;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class Util {

    /**
     * Get paginated models for specific eloquent model and http request. Request must contain following params:
     * skip - How many resources to skip (e.g. 30)
     * limit - How many resources to retreive (e.g. 15)
     * sort - Filed on which to sort returned resources (e.g. 'first_name')
     * order - Ordering of returend resources ('asc' or 'desc')
     *
     * @param Request $request request object received
     * @param Model $model model on which to apply pagination
     */
    public static function paginate(Request $request, Model $model) {
        $skip = $request->input('skip') ?? -1;
        $limit = $request->input('limit') ?? -1;
        $sort = $request->input('sort') ?? '';
        $order = $request->input('order') ?? 'asc';
        $modelsQuery = $model->skip($skip)->take($limit);
        $models = ($sort != '' && $order != '') ? $modelsQuery->orderBy($sort, $order)->get() : $modelsQuery->get();
        return $models;
    }

}