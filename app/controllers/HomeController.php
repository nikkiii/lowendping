<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function showHome() {
		return View::make('home')->with('servers', Config::get('lowendping.servers'));
	}
	
	public function submitQuery() {
		Validator::extend('validtype', function($attribute, $value, $parameters) {
			return array_key_exists($value, Config::get('lowendping.querytypes'));
		});
		
		$validator = Validator::make(Input::all(),
			array(
				'query' => array('required'),
				'servers' => array('required', 'array'),
				'type' => array('required', 'validtype')
			)
		);
		
		if ($validator->fails()) {
			return Response::json(array('success' => false, 'error' => $validator->messages()->first()));
		}
		
		$query = Input::get('query');
		$queryServers = Input::get('servers');
		$type = Input::get('type');
		
		$servers = Config::get('lowendping.servers');
		
		$serverIds = array();
		
		// Validate servers
		foreach ($queryServers as $id => $val) {
			if (!isset($servers[$id])) {
				return Response::json(array('success' => false, 'error' => 'Invalid server ' . $id));
			}
			$serverIds[] = $id;
		}
		
		if (!$this->checkQueryType($query, 4) && !$this->checkQueryType($query, 6)) {
			// Resolve it (it'll fail filter validation if it doesn't resolve)
			$check = gethostbyname($query);
			
			if (!$this->checkQueryType($check, 4) && !$this->checkQueryType($check, 6)) {
				return Response::json(array('success' => false, 'error' => 'Invalid query ' . $check));
			}
		}
		
		if ($this->checkQueryType($query, 4) && ($type == 'ping6' || $type == 'trace6' || $type == 'mtr6')) {
			return Response::json(array('success' => false, 'error' => 'Invalid address for type ' . $type));
		}
		
		$q = new Query;
		$q->query = $query;
		$q->servers = serialize($serverIds);
		$q->save();
		
		Queue::push('QueryJob', array('id' => $q->id, 'query' => $query, 'type' => $type, 'servers' => $serverIds));
		
		return Response::json(array('success' => true, 'queryid' => $q->id, 'serverCount' => count($queryServers)));
	}
	
	private function checkQueryType($query, $type = 4) {
		if ($type == 4 && filter_var($query, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
			return true;
		} else if ($type == 6 && filter_var($query, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return true;
		}
		return false;
	}
	
	public function checkResponses(Query $query) {
		$out = array();
		
		foreach ($query->responses()->unsent()->get() as $response) {
			// TODO timeout on it until it's cleared? For now just delete it.
			$response->delete();
			
			$out[] = array('id' => $response->server_id, 'data' => $response->response);
		}
		
		return Response::json($out);
	}
	
	public function serverResponse() {
		if (!Input::has('id') || !Input::has('serverid') || !Input::has('response')) {
			return Response::json(array('success' => false, 'error' => 'Missing fields!'));
		}
		
		$query = Query::find(Input::get('id'));
		
		if (!$query) {
			return Response::json(array('success' => false, 'error' => 'Invalid query!'));
		}
		
		$serverid = Input::get('serverid');
		
		$servers = Config::get('lowendping.servers');
		
		if (!isset($servers[$serverid])) {
			return Response::json(array('success' => false, 'error' => 'Invalid server!'));
		}
		
		if (QueryResponse::where('server_id', $serverid)->where('query_id', $query->id)->count() > 0) {
			return Response::json(array('success' => false, 'error' => 'Response already logged!'));
		}
		
		if (!Input::has('auth') || Input::get('auth') != $servers[$serverid]['auth']) {
			// Should we be doing this? It makes sense since it could be a valid error.
			$response = new QueryResponse;
			$response->server_id = $serverid;
			$response->response = 'Invalid response authentication.';
			$query->responses()->save($response);
			return Response::json(array('success' => false, 'error' => 'Invalid authentication!'));
		}
		
		$response = new QueryResponse;
		$response->server_id = $serverid;
		$response->response = Input::get('response');
		
		$query->responses()->save($response);
	}

}