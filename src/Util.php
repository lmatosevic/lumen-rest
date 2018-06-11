<?php

namespace Lujo\Lumen\Rest;

use Illuminate\Http\Request;

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

}