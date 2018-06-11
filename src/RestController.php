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
        $models = Util::paginate($request, $this->getModel());
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
        $model = $this->getModel()->find($id);
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
        return $this->successResponse(['id' => $model->id]);
    }

    /**
     * Update existing model.
     *
     * @param Request $request
     * @param $id number An id of model which to update.
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id) {
        $model = $this->getModel()->find($id);
        if ($model == null) {
            return $this->errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $data = $this->beforeUpdate($request);
        $model->fill($data)->save();
        return $this->successResponse(['id' => $id]);
    }

    /**
     * Delete existing model.
     *
     * @param $id number An id of model which to delete.
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id) {
        $model = $this->getModel()->find($id);
        if ($model == null) {
            return $this->errorResponse(['reason' => "Entity with {$id} id does not exist"], 404);
        }
        $this->beforeDelete($model);
        $model->delete();
        return $this->successResponse(['id' => $id]);
    }

    /**
     * Returns the successful JSON response.
     *
     * @param $data mixed Data to return to the client.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data) {
        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /**
     * Returns the error JSON response.
     *
     * @param $data mixed Data to return to the client.
     * @param $code number HTTP error code.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($data, $code) {
        return response()->json(['success' => false, 'data' => $data], $code);
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
