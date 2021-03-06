<?php
/**
 * Created by PhpStorm.
 * User: linroid
 * Date: 7/24/15
 * Time: 16:02
 */

namespace app\Http\Controllers\Api;


use App;
use App\BindToken;
use App\Device;
use App\DUAuth;
use App\Token;
use Carbon\Carbon;
use DB;
use Input;
use Lang;
use Request;
use Response;
use Validator;

class DeviceController extends ApiController {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index() {
		$device = Device::current();
		$devices = Device::where(function($query) use($device) {
							$query->whereUserId($device->user_id)
								->where('id', '<>', $device->id);
						})
						->orWhere(function($query) use($device) {
							$authed_device_ids = DUAuth::whereUserId($device->user_id)->lists('device_id');
							$query->whereIn('id', $authed_device_ids);
						})
						->paginate(15);

		return Response::json($devices);
	}

	/**
	 * 设备信息更新
	 * Update the specified resource in storage.
	 *
	 * @param  Request $request
	 * @param  int $id
	 * @return Response
	 */
	public function update(Request $request, $id) {
		$device = Device::current();
		if ($device->id != $id) {
			App::abort(403);
		}
		$data = $request::only(['alias', 'network_type']);
		$data = array_filter($data, 'strlen');
		$validator = Validator::make($data, Device::update_rules($device->user_id), Device::messages());
		if ($validator->fails()) {
			return Response::errors($validator->errors(), 400);
		}
		$device->fill($data);
		$device->save();
		return Response::json($device);
	}

	/**
	 * 获得设备二维码，用于其他设备向这台设备安装
	 */
	public function token() {
		return Response::json(Token::obtainDevice());
	}
}