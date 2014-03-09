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
		$validator = Validator::make(Input::all(),
			array(
				'query' => array('required'),
				'servers' => array('required', 'array'),
				'type' => array('required', 'in:ping,trace')
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
		foreach ($queryServers as $id) {
			if (!isset($servers[$id])) {
				return Response::json(array('success' => false, 'error' => 'Invalid server ' . $id));
			}
			$serverIds[] = $id;
		}
		
		// Resolve it (it'll fail filter validation if it doesn't resolve)
		$check = gethostbyname($query);
		
		$ipv6 = filter_var($check, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE) !== false;
		
		if (!filter_var($check, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE) && !$ipv6) {
			return Response::json(array('success' => false, 'error' => 'Invalid query ' . $check));
		}
		
		// Cheap trick for ping6 since I don't know enough Python to check ip type.
		if ($ipv6 && $type == 'ping') {
			$type = 'ping6';
		}
		
		$q = new Query;
		$q->query = $query;
		$q->servers = serialize($serverIds);
		$q->save();
		
		Queue::push('QueryJob', array('id' => $q->id, 'query' => $query, 'type' => $type));
		
		return Response::json(array('success' => true, 'queryid' => $q->id, 'serverCount' => count($queryServers)));
	}
	
	public function checkResponses(Query $query) {
		$out = array();
		
		foreach ($query->responses()->unsent()->get() as $response) {
			// TODO timeout on it until it's cleared?
			$response->sent = true;
			$response->save();
			
			$out[] = array('id' => $response->server_id, 'data' => $response->response);
		}
		
		return Response::json($out);
	}
	
	public function serverResponse() {
		$query = Query::find(Input::get('id'));
		
		if (!$query) {
			return Response::json(array('success' => false, 'error' => 'Invalid query!'));
		}
		
		$serverid = Input::get('serverid');
		$response = Input::get('response');
		
		$servers = Config::get('lowendping.servers');
		
		if (!isset($servers[$serverid])) {
			return Response::json(array('success' => false, 'error' => 'Invalid server!'));
		}
		
		$response = new QueryResponse;
		$response->server_id = $serverid;
		$response->response = Input::get('response');
		
		$query->responses()->save($response);
	}

}