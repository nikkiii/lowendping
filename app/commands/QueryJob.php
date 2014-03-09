<?php

class QueryJob {
	
	public function fire($job, $data) {
		$servers = Config::get('lowendping.servers');
		
		foreach ($servers as $id => $server) {
			$data['serverid'] = $id;
			$this->queryServer($id, $server, $data);
		}
		
		$job->delete();
	}
	
	private function queryServer($id, $server, $data) {
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