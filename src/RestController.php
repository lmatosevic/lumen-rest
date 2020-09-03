<?php

namespace Lujo\Lumen\Rest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

abstract class RestController extends BaseController {

    /**
     * Return all the models for specific resource. Result set can be modified using GET URL params:
     * skip - How many resources to skip (e.g. 30)
     * limit - How many resources to retreive (e.g. 15)
     * sort - Filed on which to sort returned resources (e.g. 'first_name')
     * order - Ordering of returend resources ('asc' or 'desc')
     *
     * Also this method can return following count headers, primarly used for paginated requests:
     * X-Result-Count - number of items in resulting set that match search criteria (number of returned items)
     * X-Total-Count - number of total items in database that match search criteria (without skip, limit params)
     *
     * Headers can be disabled by overriding and returning false value from withCountHeaders($request) method.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $with = $this->getWith($request, 'INDEX');
        $where = $this->getWhere($request, 'INDEX');
        $query = $this->getWhereFunction($request, 'INDEX');
        list($indexQuery, $totalCount) = Util::prepareQueryWithCount($request, $this->getModel(), $with, $where, $query);
        $models = $indexQuery->get();
        for ($i = 0; $i < count($models); $i++) {
            $models[$i] = $this->beforeGet($models[$i], $request);
        }
        if ($this->withCountMetadata($request) === true) {
            $response = response()->json([
                'result_count' => count($models),
                'total_count' => $totalCount,
                'data' => $models
            ]);
        } else {
            $response = response()->json($models);
        }
        if ($this->withCountHeaders($request) === true) {
            $response->withHeaders(['X-Result-Count' => count($models), 'X-Total-Count' => $totalCount]);
        }
        return $response;
    }

    /**
     * Return one model with specific id.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function one(Request $request, $id) {
        $with = $this->getWith($request, 'ONE');
        $where = $this->getWhere($request, 'ONE');
        $query = $this->getWhereFunction($request, 'ONE');
        $model = Util::prepareQuery(null, $this->getModel(), $with, $where, $query)->find($id);
        if ($model == null) {
            return Util::errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $model = $this->beforeGet($model, $request);
        return response()->json($model);
    }

    /**
     * Create new model. Returns the id of newly created model.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $data = $this->beforeCreate($request);
        if ($data) {
            $model = $this->getModel()->query()->create($data);
            $after = $this->afterCreate($request, $model);
            if ($after) {
                return $after;
            }
            return Util::successResponse(['id' => $model->id], 201);
        } else {
            return Util::successResponse(['id' => null, 'description' => 'Action avoided']);
        }
    }

    /**
     * Update existing model.
     *
     * @param Request $request
     * @param $id number An id of model which to update.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id) {
        $where = $this->getWhere($request, 'UPDATE');
        $query = $this->getWhereFunction($request, 'UPDATE');
        $model = Util::prepareQuery(null, $this->getModel(), [], $where, $query)->find($id);
        if ($model == null) {
            return Util::errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $data = $this->beforeUpdate($request);
        if ($data) {
            $model->fill($data)->save();
            $after = $this->afterUpdate($request, $model);
            if ($after) {
                return $after;
            }
            return Util::successResponse(['id' => $id], 204);
        } else {
            return Util::successResponse(['id' => $id, 'description' => 'Action avoided']);
        }
    }

    /**
     * Delete existing model.
     *
     * @param Request $request
     * @param $id number An id of model which to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id) {
        $where = $this->getWhere($request, 'DELETE');
        $query = $this->getWhereFunction($request, 'DELETE');
        $model = Util::prepareQuery(null, $this->getModel(), [], $where, $query)->find($id);
        if ($model == null) {
            return Util::errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $result = $this->beforeDelete($model, $request);
        if ($result) {
            $model->delete();
            $after= $this->afterDelete($request, $model);
            if ($after) {
                return $after;
            }
            return Util::successResponse(['id' => $id], 202);
        } else {
            return Util::successResponse(['id' => $id, 'description' => 'Action avoided']);
        }
    }

    /**
     * Called on index and one functions to specify list of realtions of current resource to be returend.
     *
     * @param Request $request
     * @param string $action An function called on this request. (INDEX or ONE)
     * @return array Array of relations to be included in returned model/models.
     */
    protected function getWith($request, $action) {
        return [];
    }

    /**
     * Called on index, one, update and delete functions to specify condition on which to filter data.
     *
     * @param Request $request
     * @param string $action An function called on this request. (INDEX, ONE, UPDATE or DELETE)
     * @return array Array of conditions on which to return models.
     */
    protected function getWhere($request, $action) {
        return [];
    }

    /**
     * Called on index, one, update and delete functions to specify additional where query statement in form of a
     * function: e.g. function($query) { $query->where(...)->orWhere(...)->orWhere(...); }
     *
     * @param Request $request
     * @param string $action An function called on this request. (INDEX, ONE, UPDATE or DELETE)
     * @return callable Function to be executed while querying data.
     */
    protected function getWhereFunction($request, $action) {
        return null;
    }

    /**
     * Called before returning model from controller, can retrun updated model with some extra data.
     *
     * @param $model Model
     * @param $request Request
     * @return Model
     */
    protected function beforeGet($model, $request) {
        return $model;
    }

    /**
     * Called before creating new model with request data, used for adding additional data or updating existing data
     * from the request. Return null or false to avoid creating model.
     *
     * @param $request Request
     * @return mixed|boolean|null
     */
    protected function beforeCreate($request) {
        return $request->all();
    }

    /**
     * Called before updating existing model with request data, used for adding additional data or updating existing
     * data from the request. Return null or false to avoid updating model.
     *
     * @param $request Request
     * @return mixed|boolean|null
     */
    protected function beforeUpdate($request) {
        return $request->all();
    }

    /**
     * Called before deleting model from database. Return null or false to avoid deleting model.
     *
     * @param $model Model
     * @param $request Request
     * @return boolean|null
     */
    protected function beforeDelete($model, $request) {
        return true;
    }

    /**
     * Called after the model is successfully created. Here is possible to perform event logging, notifications or return
     * alternative resposne that should be returned from this endpoint.
     *
     * @param $request Request
     * @param $model Model
     */
    protected function afterCreate($request, $model) {
        // no-op
    }

    /**
     * Called after the model is successfully updated. Here is possible to perform event logging, notifications or return
     * alternative resposne that should be returned from this endpoint.
     *
     * @param $request Request
     * @param $model Model
     */
    protected function afterUpdate($request, $model) {
        // no-op
    }

    /**
     * Called after the model is successfully deleted. Here is possible to perform event levent logging, notifications
     * or return alternative resposne that should be returned from this endpoint.
     *
     * @param $request Request
     * @param $model Model
     */
    protected function afterDelete($request, $model) {
        // no-op
    }

    /**
     * Called before returning models from database using INDEX method. Return null or false to avoid adding the
     * additional headers to the response (X-Result-Count and X-Total-Count).
     *
     * @param $request Request
     * @return boolean|null
     */
    protected function withCountHeaders($request) {
        return true;
    }

    /**
     * Called before returning models from database using INDEX method. Return true to add the additional metadata to
     * the response JSON (result_count and total_count) and store resulting array inside data field.
     * E.g. {result_count: 10, total_count: 45, data: [...results]}.
     *
     * @param Request $request
     * @return boolean|null
     */
    protected function withCountMetadata($request) {
        return false;
    }

    /**
     * Return the specific model object of a resource for child controller.
     *
     * @return Model
     */
    protected abstract function getModel();
}
