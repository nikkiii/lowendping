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
	
	public function showResult(Query $query) {
		$servers = Config::get('lowendping.servers');
		$responses = array();
		
		foreach ($query->responses()->orderBy('server_id', 'asc')->get() as $response) {
			$response->server = $servers[$response->server_id];
			$responses[] = $response;
		}
		
		$query->expire_at = $query->created_at->addDays(Config::get('lowendping.archive.days', 7));
		
		return View::make('result')->with('query', $query)->with('responses', $responses);
	}
	
	public function submitQuery() {
		$validator = Validator::make(Input::all(),
			array(
				'query' => array('required', 'query'),
				'servers' => array('required', 'array'),
				'type' => array('required', 'type')
			)
		);
		
		if ($validator->fails()) {
			return Response::json(array('success' => false, 'error' => $validator->messages()->first()));
		}
		
		// Validate rate limit
		$ip = Request::getClientIp();
		
		$querylimit = Config::get('lowendping.ratelimit.queries');
		
		if (!empty($querylimit)) {
			$limit = RateLimit::find($ip);
			
			if ($limit) {
				$time = time();
				
				$expiration = (int) ($limit->time + Config::get('lowendping.ratelimit.timespan'));
				
				if ($expiration > $time) {
					if ($limit->hits >= $querylimit) {
						$reset = ($expiration - $time) / 60;
						if ($reset <= 1) {
							return Response::json(array('success' => false, 'error' => 'Rate limit exceeded, please try again in 1 minute.'));
						}
						return Response::json(array('success' => false, 'error' => 'Rate limit exceeded, please try again in ' . $reset . ' minutes.'));
					}
					
					$limit->hits++;
					$limit->save();
				} else {
					$limit->time = time();
					$limit->hits = 1;
					$limit->save();
				}
			} else {
				$limit = new RateLimit;
				$limit->ip = $ip;
				$limit->hits = 1;
				$limit->time = time();
				$limit->save();
			}
		}
		
		$servers = Config::get('lowendping.servers');
		
		$serverIds = array();
		
		// Validate servers
		foreach (Input::get('servers') as $id => $val) {
			if (!isset($servers[$id])) {
				return Response::json(array('success' => false, 'error' => 'Invalid server ' . $id));
			}
			$serverIds[] = $id;
		}

		// Process the query
		$query = Input::get('query');
		
		$q = new Query;
		$q->query = $query;
		$q->servers = serialize($serverIds);
		$q->save();
		
		Queue::push('QueryJob', array('id' => $q->id, 'query' => $query, 'type' => Input::get('type'), 'servers' => $serverIds));
		
		$response = array(
			'success' => true,
			'queryid' => $q->id,
			'serverCount' => count($serverIds)
		);
		
		if (Config::get('lowendping.archive.enabled', false)) {
			$response['resultLink'] = action('HomeController@showResult', array('query' => $q->id));
		}
		
		return Response::json($response);
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
		
		$archive = Config::get('lowendping.archive.enabled', false);
		
		foreach ($query->responses()->unsent()->get() as $response) {
			if ($archive) {
				$response->sent = 1;
				$response->save();
			} else {
				$response->delete();
			}
			
			$out[] = array('id' => $response->server_id, 'data' => $response->response);
		}
		
		return Response::json($out);
	}
	
	public function serverResponse() {
		$validator = Validator::make(Input::all(),
			array(
				'id' => array('required', 'exists:queries,id'),
				'serverid' => array('required', 'server'),
				'response' => array('required'),
				'auth' => array('required')
			)
		);
		
		if ($validator->fails()) {
			return Response::json(array('success' => false, 'error' => $validator->messages()->first()));
		}
		
		$query = Query::find(Input::get('id'));
		
		$serverid = Input::get('serverid');
		
		$servers = Config::get('lowendping.servers');
		
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