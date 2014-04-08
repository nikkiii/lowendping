<?php

class QueryJob {
	
	public function fire($job, $data) {
		$servers = Config::get('lowendping.servers');
		
		foreach ($data['servers'] as $id) {
			$this->queryServer($id, $servers[$id], $data);
		}
		
		$job->delete();
	}
	
	private function queryServer($id, $server, $data) {
		// Add required fields
		$data['serverid'] = $id;
		$data['auth'] = $server['auth'];
		// Connect and send the data
		$fs = @fsockopen($server['host'], $server['port'], $errno, $errstr, 5);
		if (!$fs) {
			// mark as unable to connect so we aren't waiting forever
			$resp = new QueryResponse;
			$resp->query_id = $data['id'];
			$resp->server_id = $id;
			$resp->response = 'Failed to connect.';
			$resp->save();
			return;
		}
		fwrite($fs, json_encode($data));
		fclose($fs);
	}
	
}