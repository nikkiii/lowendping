<?php

class ApiController extends BaseController {
	
	public function __construct() {
		$this->beforeFilter(function() {
			if (!Config::get('lowendping.api.enabled', false)) {
				return Response::make('API Disabled', 403);
			}
		});
	}

	public function serverList() {
		$list = array();
	
		foreach (Config::get('lowendping.servers') as $server => $info) {
			$list[$server] = $server['name'];
		}
	
		return Response::json($list);
	}
	
}