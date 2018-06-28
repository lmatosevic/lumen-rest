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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        $models = Util::prepareQuery($request, $this->getModel(), $this->getWith('INDEX'), $this->getWhere('INDEX'))->get();
        for ($i = 0; $i < count($models); $i++) {
            $models[$i] = $this->beforeGet($models[$i]);
        }
        return response()->json($models);
    }

    /**
     * Return one model with specific id.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function one($id) {
        $model = Util::prepareQuery(null, $this->getModel(), $this->getWith('ONE'), $this->getWhere('ONE'))->find($id);
        $model = $this->beforeGet($model);
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
        $model = $this->getModel()->query()->create($data);
        return Util::successResponse(['id' => $model->id], 201);
    }

    /**
     * Update existing model.
     *
     * @param Request $request
     * @param $id number An id of model which to update.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id) {
        $model = Util::prepareQuery(null, $this->getModel(), [], $this->getWhere('UPDATE'))->find($id);
        if ($model == null) {
            return Util::errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $data = $this->beforeUpdate($request);
        $model->fill($data)->save();
        return Util::successResponse(['id' => $id], 204);
    }

    /**
     * Delete existing model.
     *
     * @param $id number An id of model which to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id) {
        $model = Util::prepareQuery(null, $this->getModel(), [], $this->getWhere('DELETE'))->find($id);
        if ($model == null) {
            return Util::errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $this->beforeDelete($model);
        $model->delete();
        return Util::successResponse(['id' => $id], 202);
    }

    /**
     * Called on index and one functions to specify list of realtions of current resource to be returend.
     *
     * @param string $action An function called on this request. (INDEX or ONE)
     * @return array Array of relations to be included in returned model/models.
     */
    protected function getWith($action) {
        return [];
    }

    /**
     * Called on index, one, update, delete functions to specify condition on which to filter data.
     *
     * @param string $action An function called on this request. (INDEX, ONE, UPDATE or DELETE)
     * @return array Array of conditions on which to return models.
     */
    protected function getWhere($action) {
        return [];
    }

    /**
     * Called before returning model from controller, can retrun updated model with some extra data.
     *
     * @param $model Model
     * @return Model
     */
    protected function beforeGet($model) {
        return $model;
    }

    /**
     * Called before creating new model with request data, used for adding additional data or updating existing data
     * from the request.
     *
     * @param $request Request
     * @return mixed
     */
    protected function beforeCreate($request) {
        return $request->all();
    }

    /**
     * Called before updating existing model with request data, used for adding additional data or updating existing
     * data from the request.
     *
     * @param $request Request
     * @return mixed
     */
    protected function beforeUpdate($request) {
        return $request->all();
    }

    /**
     * Called before deleting model from database.
     *
     * @param $model Model
     * @return null
     */
    protected function beforeDelete($model) {
        return null;
    }

    /**
     * Return the specific model object of a resource for child controller.
     *
     * @return Model
     */
    protected abstract function getModel();
}
